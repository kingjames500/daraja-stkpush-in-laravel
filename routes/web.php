<?php
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\customer2Business;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/index', function () {
    return view('home');
});
//route groups for mpesa stkpush, reversal and transaction status, and callback
Route::controller(MpesaController::class)
->prefix('test')
->as('test')
->group(function(){
    route::get('/token', 'generateToken')->name('token');
    route::get('/reversetransaction', 'reverseTransaction')->name('reversetransaction');
    route::post('/timeout', 'reversalTimeout')->name('timeout');
    route::post('/callback', 'stkPushCallback')->name('callback');
    route::post('/reversetransaction', 'reversalResults')->name('reversetransaction');
    route::get('/callback', 'stkPushCallback')->name('callback');
    Route::get('/qrcode', 'qrCode')->name('qrcode');
});
route::get('/mpesa/{status?}', [MpesaController::class, 'fetchTransactions'])->name('transactions');

route::post('/stkstatus',[MpesaController::class, 'stkQuery'])->name('stkstatus');

route::post('/stkpush', [MpesaController::class, 'initiateStkSimulation'])->name('stkpush');

Route::get('/stkpush', function () {
    return view('index');
});
Route::get('/stkstatus', function () {
    return view('stkquery');
});



