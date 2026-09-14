<?php

namespace App\Filament\Owner\Resources\StaffMembers;

use App\Enums\AssistantPermission;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Owner\Resources\StaffMembers\Pages\ManageStaffMembers;
use App\Models\Membership;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffMemberResource extends Resource
{
    protected static ?string $model = Membership::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Staff';

    protected static ?string $modelLabel = 'staff member';

    protected static ?string $slug = 'staff';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', Filament::getTenant()?->getKey())
            ->whereIn('role', [UserRole::Assistant, UserRole::Agent, UserRole::OrgAdmin]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->relationship('user', 'name')->required()->searchable(),
            Select::make('role')->options([
                UserRole::Assistant->value => UserRole::Assistant->getLabel(),
                UserRole::Agent->value => UserRole::Agent->getLabel(),
                UserRole::OrgAdmin->value => UserRole::OrgAdmin->getLabel(),
            ])->required(),
            CheckboxList::make('permissions')
                ->options(collect(AssistantPermission::cases())->mapWithKeys(
                    fn (AssistantPermission $permission) => [$permission->value => config('rentos.assistant_permissions.'.$permission->value, $permission->value)]
                ))
                ->columns(2)
                ->columnSpanFull(),
            Toggle::make('is_point_of_contact'),
            Select::make('status')->options(UserStatus::class)->default(UserStatus::Active)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable(),
                TextColumn::make('user.email'),
                TextColumn::make('role')->badge(),
                IconColumn::make('is_point_of_contact')->boolean()->label('PoC'),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStaffMembers::route('/'),
        ];
    }
}
