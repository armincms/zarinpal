<?php

namespace Armincms\Zarinpal;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{Text, Select};
use Armincms\Arminpay\Contracts\{Gateway, Billing}; 
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Invoice;

class Zarinpal implements Gateway
{ 
    /**
     * The gateway configuration values.
     * 
     * @var array
     */
    public $config = [];    

    /**
     * Construcy the instance.
     * 
     * @param array $config 
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Make payment for the given Billing.
     * 
     * @param  \Illuminate\Http\Request  $request  
     * @param  \Armincms\Arminpay\Contracts\Billing $billing  
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @throws \InvalidArgumentException
     */
    public function pay(Request $request, Billing $billing)
    {    
        return Payment::via('zarinpal')
                        ->config(array_merge($this->getConfigurations(), [
                        	'callbackUrl' => $billing->callback(),
                        ]))
                        ->purchase($this->newInvoice($billing))
                        ->callbackUrl($billing->callback())
                        ->pay();
    } 

    public function newInvoice(Billing $billing)
    {
        return tap(new Invoice, function($invoice) use ($billing) {
            $invoice->amount(currency($billing->amount(), $billing->currency(), 'IRT', false))
                    ->uuid($billing->getIdentifier());
        });
    }

    /**
     * Verify the payment for the given Billing.
     * 
     * @param  \Illuminate\Http\Request  $request  
     * @param  \Armincms\Arminpay\Contracts\Billing $billing  
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @throws \InvalidArgumentException
     */
    public function verify(Request $request, Billing $billing)
    {
        return Payment::amount(currency($billing->amount(), $billing->currency(), 'IRT', false))
                    ->via('zarinpal')
                    ->config($this->getConfigurations())
                    ->transactionId($billing->getIdentifier())
                    ->verify()
                    ->getReferenceId();
    } 
 
    /**
     * Returns configuration fields.
     * 
     * @return array 
     */
    public function fields(Request $request): array
    {
        return [ 
            Text::make('Merchant ID', 'merchantId')
                ->help(__('Please enter the given the Zarinpal bank Merchant Id.'))
                ->required()
                ->rules('required'), 

            Select::make(__('Mode'), 'mode')->options([
                'normal' => __('Normal (default)'),
                'sandbox' => __('Sandbox'),
                'zaringate' => __('Zaringate'),
            ])
            ->required()
            ->rules('required'),
        ];
    }  

    public function getConfigurations()
    {   
        return $this->config; 
    }
}
