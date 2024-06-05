<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\OrderResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LastestOrders extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery()
           // ->defaultPaginationPageOption(5)
            /*->defaultSort('created_At','desc')*/ )
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
                ->badge()
                ->searchable(),

            Tables\Columns\TextColumn::make('payment_status')
                ->label('Payment Status')
                ->sortable()
                ->badge()
                ->searchable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Order Status')
                ->badge()
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
            
            ->actions([
             Action::make('View Order')
             ->url(fn(Order $record): string => OrderResource::getUrl('view',['record'=>$record]))
             ->icon('heroicon-m-eye'),
            ]);
    }
}
