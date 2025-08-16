<?php

namespace App\Lunar\Extensions;

use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Lunar\Admin\Support\Extending\EditPageExtension;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Forms\Components\TranslatedText;

class ProductEditExtension extends EditPageExtension
{
    public function extendForm(Form $form): Form
    {
        return $form->schema([
            Section::make('Basic Information')
                ->schema([
                    TranslatedText::make('attribute_data.name')
                        ->label('Name')
                        ->required(),
                    TranslatedText::make('attribute_data.description')
                        ->label('Description'),
                    Attributes::make()
                        ->statePath('attribute_data'),
                        
                ])
                ->columns(1),
            ...$form->getComponents(true), // Gets the currently registered components
        ]);
    }
}   