<?php

namespace App\Services\Ai;

use App\Models\Application;
use ImranDevBd\AiHub\Facades\AIHub;
use Throwable;

class ScreeningAiService
{
    public function score(Application $application): Application
    {
        $prompt = <<<PROMPT
Score this rental application 0-100 (higher is stronger) and explain briefly.
Return JSON {"risk_score": number, "risk_notes": string}.
Income (minor units): {$application->monthly_income}
Employer: {$application->employer}
Employment: {$application->employment_status}
Occupants: {$application->occupants}
Pets: {$application->has_pets}
Do not discriminate on protected characteristics. Focus on ability-to-pay and completeness.
PROMPT;

        try {
            $data = AIHub::prompt($prompt)
                ->forJob('application-screening')
                ->recoverJson()
                ->send()
                ->json();

            $application->forceFill([
                'risk_score' => max(0, min(100, (int) ($data['risk_score'] ?? 50))),
                'risk_notes' => (string) ($data['risk_notes'] ?? 'Manual review recommended.'),
            ])->save();
        } catch (Throwable) {
            $incomeToRent = 0;
            $rent = $application->listing?->rent ?: 1;
            if ($application->monthly_income && $rent) {
                $incomeToRent = $application->monthly_income / $rent;
            }

            $application->forceFill([
                'risk_score' => $incomeToRent >= 3 ? 80 : ($incomeToRent >= 2 ? 60 : 40),
                'risk_notes' => 'Heuristic fallback (AI unavailable): income-to-rent ratio '.round($incomeToRent, 2).'.',
            ])->save();
        }

        return $application->refresh();
    }
}
