<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Pages\CreateAddress;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Group::make()->schema([
                    Section::make('Order Information')->schema([
                        Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('payment_method')
                            ->options([
                                'stripe' => 'Stripe',
                                'cod' => 'Cash on delivery',
                            ])->required(),

                        Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required(),

                        ToggleButtons::make('status')
                            ->inline()
                            ->default('new')
                            ->required()
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->colors([
                                'new' => 'info',
                                'processing' => 'warning',
                                'shipped' => 'success',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->icons([
                                'new' => 'heroicon-m-sparkles',
                                'processing' => 'heroicon-m-arrow-path',
                                'shipped' => 'heroicon-m-truck',
                                'delivered' => 'heroicon-m-check-badge',
                                'cancelled' => 'heroicon-m-x-circle',
                            ]),

                        Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                                'JPY' => 'JPY',
                                'AUD' => 'AUD',
                                'CAD' => 'CAD',
                                'CNY' => 'CNY',
                                'NZD' => 'NZD',
                                'CHF' => 'CHF',
                                'SEK' => 'SEK',
                                'NOK' => 'NOK',
                                'DKK' => 'DKK',
                                'HKD' => 'HKD',
                                'SGD' => 'SGD',
                                'THB' => 'THB',
                                'MYR' => 'MYR',
                                'PHP' => 'PHP',
                                'IDR' => 'IDR',
                                'VND' => 'VND',
                                'KRW' => 'KRW',
                                'INR' => 'INR',
                                'RUB' => 'RUB',
                                'TRY' => 'TRY',
                                'ILS' => 'ILS',
                                'MXN' => 'MXN',
                                'BRL' => 'BRL',
                                'ZAR' => 'ZAR',
                                'CZK' => 'CZK',
                                'PLN' => 'PLN',
                            ])
                            ->default('USD')
                            ->required(),

                        Select::make('shipping_method')
                            ->options([
                                'fedex' => 'FedEx',
                                'ups' => 'UPS',
                                'dhl' => 'DHL',
                                'usps' => 'USPS',
                            ])->required(),

                        Textarea::make('notes')
                            ->columnSpanFull()
                    ])->columns(2),

                    Section::make('Order Items')->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(4)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = Product::find($state)?->price ?? 0;
                                        $quantity = $get('quantity') ?? 1;
                                        $set('unit_amount', $price);
                                        $set('total_amount', $price * $quantity);
                                    }),
                                    

                                    TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minvalue(1)
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount'))),
                                    

                                    TextInput::make('unit_amount')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->columnSpan(3),

                                    TextInput::make('total_amount')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(3)
                                    ->dehydrated(),
                            ])->columns(12),

                            Placeholder::make("grand_total_placeholder")
                            ->label('Grand Total')
                            ->content(function(Get $get, Set $set) {
                                $total = 0;
                                if ($items = $get("items")) {
                                    foreach ($items as $item) {
                                        $total += $item['total_amount'];
                                    }
                                }
                                $set('grand_total',$total);
                                return Number::currency($total,'USD');
                                Hidden::make('grand_total')->default(0);
                                // Formatting the total to 2 decimal places
                            })->columnSpanFull() // Optionally make it span full width if needed
                        

                            
                    ]),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Order Status')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('currency')
                    ->label('Currency')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('shipping_method')
                    ->label('Shipping Method')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => Number::currency($state, 'USD')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->dateTime('Y-m-d H:i:s')
                    ->toggleable(isToggledHiddenByDefault:true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->sortable()
                    ->dateTime('Y-m-d H:i:s')
                    ->toggleable(isToggledHiddenByDefault:true),

            ])
            ->filters([
                // يمكن إضافة الفلاتر هنا
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            // يمكن إضافة العلاقات هنا
            AddressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];

        
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        $orderItems = collect($data['items'] ?? []);
        $grandTotal = $orderItems->sum('total_amount');
        $data['grand_total'] = $grandTotal;

        return $data;
    }

}
