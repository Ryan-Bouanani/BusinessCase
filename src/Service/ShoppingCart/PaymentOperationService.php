<?php

namespace App\Service\ShoppingCart;

use Omnipay\Omnipay;
use App\Entity\Basket;
use Stripe\StripeClient;
use App\Repository\BasketRepository;
use Omnipay\Common\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\ResponseInterface;
use Stripe\Checkout\Session;

class PaymentOperationService
{

    private $paypalGateway;
    private $stripeGateway;
    private $basketRepository;


    public function __construct(
        BasketRepository $basketRepository,
    )
    {
        $this->paypalGateway=Omnipay::create('PayPal_Rest');
        $this->paypalGateway->setSecret($_ENV['PAYPAL_SECRET_KEY']);
        $this->paypalGateway->setClientId($_ENV['PAYPAL_CLIENT_ID']);
        $this->paypalGateway->setTestMode(true);
        $this->basketRepository = $basketRepository;

        $this->stripeGateway = new StripeClient($_ENV['STRIPE_SECRETKEY']);
    }

    /**
     * Cette function va servir à lancer un processus d'achat avec la passerelle de paiement PayPal
     *
     * @param float $amount
     * @param string $currency
     * @param string $successUrl
     * @param string $cancelUrl
     * @param array $items
     * @return RequestInterface
     */
    public function purchase(float $amount, string $currency, string $successUrl, string $cancelUrl, array $items) : RequestInterface
    {
        $data = array(
            'amount' => $amount,
            'currency' => $currency,
            'returnUrl' => $successUrl,
            'cancelUrl' => $cancelUrl,
            'items' => $items,
        );

        return $this->paypalGateway->purchase($data);
    }



    /**
     * Cette function va servir à compléter un processus d'achat avec la passerelle de paiement PayPal.
     *
     * @param string $payerId
     * @param string $paymentId
     * @return ResponseInterface
     */
    public function completePurchase(string $payerId, string $paymentId): ResponseInterface
    {

        return $this->paypalGateway->completePurchase([
                'payerId' => $payerId,
                'transactionReference' => $paymentId,
        ])->send();
    }

    /**
     * Cette function va servir à vérifier si la transaction s'est bien passer et mettre les infos de la commande en bdd
     *
     * @param Request $request
     * @param Basket $order
     * @return void
     */
    public function completePurchaseAndSavePaypalIdPayment(Request $request, Basket $order): void
{
    $payerId = $request->query->get('PayerID');
    $paymentId = $request->query->get('paymentId');
    
    $response = $this->completePurchase($payerId, $paymentId);

    // On récupère les infos de la transaction
    $paymentData = $response->getData();

    if ($response->isSuccessful()) {
        $order->setPaypalPaymentId($paymentData['id'])
                ->setBillingDate(new \DateTime())
                // ->setStatus($paymentData['state'])
        ;
        $this->basketRepository->add($order, true);          
    } else {
        throw new InvalidResponseException('Une erreur s\'est produite lors de la validation de votre paiement. Merci de réessayer plus tard.');
    }
}

    /**
     * Cette function va servir à vérifier si la transaction s'est bien passer et mettre les infos de la commande en bdd
     *
     * @param Request $request
     * @param Basket $order
     * @return void
     */
    public function completeAndSaveStripePayment(Request $request, Basket $order): void
{
    $id_sessions = $request->query->get('id_sessions');

    // On récupère les infos de la transaction        
    $customer = $this->stripeGateway->checkout->sessions->retrieve(
        $id_sessions,
        []
    );
    
    if ($customer) {
        // Update Order in BDD
        $order->setStripeSessionId($id_sessions)
                ->setBillingDate(new \DateTime())
        ;
        $this->basketRepository->add($order, true);
    } else {
        throw new InvalidResponseException('Une erreur s\'est produite lors de la validation de votre paiement. Merci de réessayer plus tard.');
    }
}

    /**
     * Cette function va servir à vérifier que la commande à bien été payé en nous retournant le paymentId et le PayerID
     *
     * @param Basket $order
     * @param Request $request
     * @return boolean
     */
    public function isPaypalOrderUnpaid(Basket $order, Request $request): bool
    {
        return $order->getMeanOfPayment()->getDesignation() === 'Paypal' && (!$request->query->get('paymentId') || !$request->query->get('PayerID'));
    }

    /**
     * Cette function va servir à vérifier si le paiement de la commande n'est pas déjà en bdd
     *
     * @param string $meanOfPayment
     * @param Request $request
     * @return boolean
     */
    public function isExistingPayment(string $meanOfPayment, Request $request): bool
    {
        $paymentIdField = '';
        switch ($meanOfPayment) {
            case 'Paypal':
                $paymentIdField = 'paypalPaymentId';
                $paymentId = $request->query->get('paymentId');
                break;
            case 'Carte bancaire':
                $paymentIdField = 'stripeSessionId';
                $paymentId = $request->query->get('id_sessions'); 
                break;
            // Ajout de cas pour d'autres types de paiement si besoin
            default:
                throw new \InvalidArgumentException('Type de paiement invalid.');
        }
        $existingPayment = $this->basketRepository->findOneBy([$paymentIdField => $paymentId]);
        return $paymentId === null || $existingPayment !== null;
    }


    /**
     * Cette function va servir à lancer un processus d'achat avec la passerelle de paiement Stripe
     *
     * @param string $customerEmail
     * @param array $items
     * @param string $successUrl
     * @param string $cancelUrl
     * @return Session
     */
    public function checkout(string $customerEmail, array $items, string $successUrl, string $cancelUrl) : Session
    {
        $checkout = $this->stripeGateway->checkout->sessions->create([
            'customer_email' => $customerEmail,
            'payment_method_types' => ['card'],
            'line_items' => [
                    $items
            ],
              'mode' => 'payment',
              'success_url' => $successUrl,
              'cancel_url' => $cancelUrl,
        ]);
        return $checkout;
    }
}