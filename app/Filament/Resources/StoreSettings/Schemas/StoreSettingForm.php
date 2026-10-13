<?php

namespace App\Filament\Resources\StoreSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class StoreSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('key')->options([
                    'announcement'=>'Announcement bar','brand_line'=>'Brand tagline','whatsapp_number'=>'WhatsApp number','facebook_url'=>'Facebook URL',
                    'cod_active'=>'COD enabled (1=yes, 0=no)','bkash_active'=>'bKash enabled (1=yes, 0=no)','bkash_number'=>'bKash number','bkash_account_name'=>'bKash account name','bkash_instructions'=>'bKash instructions',
                    'privacy_policy'=>'Privacy policy','terms_policy'=>'Terms and conditions','return_policy'=>'Return policy',
                ])->required()->unique(ignoreRecord:true)->searchable(),
                Textarea::make('value')
                    ->columnSpanFull(),
            ]);
    }
}
