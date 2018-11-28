<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['oauth', 'web', 'menus'])->group(function () {

    Route::get('/', 'HomeController@index')->name("index");
    Route::get('ticket/list', 'TicketController@listTicket')->name('ticket.list');
    Route::get('ticket/show/{id}', 'TicketController@showTicket')->where('id', '[0-9]+')->name('ticket.show');

    Route::post('ticket/addMessage/{id}', 'TicketController@addMessage')->where('id', '[0-9]+')->name('ticket.addMessage');
    Route::post('ticket/saveAdmin/{id}', 'TicketController@saveAdmin')->where('id', '[0-9]+')->name('ticket.saveAdmin');

    Route::get('ticket/new', 'TicketController@newTicket')->name('ticket.new');
    Route::post('ticket/addNew', 'TicketController@addTicket')->name('ticket.addNew');

    Route::get('ticket/admin', 'TicketController@adminList')->name('ticket.admin');
});
