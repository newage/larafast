<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Setup extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $title = 'Setup';

    protected static ?string $navigationLabel = 'Setup';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.setup';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->currentValues());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Form::make([
                    Section::make('Claude')
                        ->description('Puste pole oznacza wartość domyślną z pliku .env.')
                        ->schema([
                            TextInput::make(Setting::API_KEY)
                                ->label('Klucz API Anthropic')
                                ->password()
                                ->autocomplete(false)
                                ->placeholder($this->maskedKey())
                                ->helperText('Zapisany klucz jest szyfrowany. Pozostaw puste, aby go nie zmieniać.'),
                            TextInput::make('CLAUDE_MODEL')
                                ->label('Model')
                                ->placeholder(config('claude.CLAUDE_MODEL')),
                            Select::make('CLAUDE_EFFORT')
                                ->label('Effort')
                                ->options(array_combine(self::EFFORTS, self::EFFORTS))
                                ->placeholder('Domyślny dla modelu')
                                ->helperText('Domyślnie z .env: '.config('claude.CLAUDE_EFFORT')),
                            TextInput::make('CLAUDE_MAX_TOKENS')
                                ->label('Maks. tokenów odpowiedzi')
                                ->numeric()->integer()->minValue(1)
                                ->placeholder((string) config('claude.CLAUDE_MAX_TOKENS')),
                            TextInput::make('MAX_MESSAGES')
                                ->label('Maks. liczba wiadomości')
                                ->numeric()->integer()->minValue(1)
                                ->placeholder((string) config('claude.MAX_MESSAGES')),
                            TextInput::make('MAX_MESSAGE_CHARS')
                                ->label('Maks. długość wiadomości (znaki)')
                                ->numeric()->integer()->minValue(1)
                                ->placeholder((string) config('claude.MAX_MESSAGE_CHARS')),
                            TextInput::make('VECTOR_MIN_SCORE')
                                ->label('Minimalny wynik wyszukiwania wektorowego')
                                ->numeric()->minValue(0)->maxValue(1)
                                ->placeholder((string) config('claude.VECTOR_MIN_SCORE')),
                        ])
                        ->columns(2),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Action::make('save')
                            ->label('Zapisz')
                            ->submit('save'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (self::KEYS as $key) {
            $value = $data[$key] ?? null;
            $value = ($value === null || $value === '') ? null : (string) $value;

            // An empty API key field keeps the stored key.
            if ($key === Setting::API_KEY && $value === null) {
                continue;
            }

            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = $value;
            $setting->save();
        }

        $this->form->fill($this->currentValues());

        Notification::make()->title('Zapisano')->success()->send();
    }

    public const EFFORTS = ['low', 'medium', 'high', 'xhigh', 'max'];

    private const KEYS = [
        Setting::API_KEY,
        'CLAUDE_MODEL',
        'CLAUDE_EFFORT',
        'CLAUDE_MAX_TOKENS',
        'MAX_MESSAGES',
        'MAX_MESSAGE_CHARS',
        'VECTOR_MIN_SCORE',
    ];

    /**
     * Saved values only; the API key is never sent back to the form.
     *
     * @return array<string, ?string>
     */
    private function currentValues(): array
    {
        return Setting::query()
            ->whereIn('key', self::KEYS)
            ->where('key', '!=', Setting::API_KEY)
            ->pluck('value', 'key')
            ->all();
    }

    private function maskedKey(): string
    {
        $key = (string) Setting::resolve(Setting::API_KEY);

        if ($key === '') {
            return 'Nie ustawiono';
        }

        return substr($key, 0, 7).'…'.substr($key, -4);
    }
}
