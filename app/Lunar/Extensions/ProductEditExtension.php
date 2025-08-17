<?php

namespace App\Lunar\Extensions;

use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\RichEditor;
use Lunar\Admin\Support\Extending\EditPageExtension;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Forms\Components\TranslatedRichEditor;

class ProductEditExtension extends EditPageExtension
{
    /**
     * @param Form $form
     * @param int $indexToRemove Index of the component to remove (default: 0)
     */
    public function extendForm(Form $form, int $indexToRemove = 4): Form
    {
        $components = $form->getComponents(true);
        // Remove the component at the given index
        if (isset($components[$indexToRemove])) {
            array_splice($components, $indexToRemove, 1);
        }

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
            ...$components,
        ]);
        
    }
}

// in this implment a new table which will store a json in which we will create a new page and a action next to the edit
// and you can add a field and remove fields, or just put a rich editor and put this in the sub navigation menu