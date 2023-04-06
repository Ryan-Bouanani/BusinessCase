<?php

namespace App\Service\ShoppingCart;

use Omnipay\Omnipay;
use App\Entity\Basket;
use App\Entity\PaypalPayment;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PaypalPaymentRepository;
use Symfony\Component\HttpFoundation\Request;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\ResponseInterface;

class PaypalOperationService
{

    private $gateway;

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->gateway=Omnipay::create('PayPal_Rest');
        $this->gateway->setSecret($_ENV['PAYPAL_SECRET_KEY']);
        $this->gateway->setClientId($_ENV['PAYPAL_CLIENT_ID']);
        $this->gateway->setTestMode(true);

        $this->manager = $manager;
    }

    /**
     * Cette function va servir à lancer un processus d'achat avec la passerelle de paiement PayPal
     *
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param array $items
     * @return RequestInterface
     */
    public function purchase(float $amount, string $currency, string $returnUrl, string $cancelUrl, array $items) : RequestInterface
    {
        $data = array(
            'amount' => $amount,
            'currency' => $currency,
            'returnUrl' => $returnUrl,
            'cancelUrl' => $cancelUrl,
            'items' => $items,
        );

        return $this->gateway->purchase($data);
    }



    /**
     * Cette function va servir à compléter un processus d'achat avec la passerelle de paiement PayPal.
     *
     * @param string $payerId
     * @param string $paymentId
     * @return void
     */
    public function completePurchase(string $payerId, string $paymentId) 
    {

        return $this->gateway->completePurchase([
                'payerId' => $payerId,
                'transactionReference' => $paymentId,
        ])->send();
    }

    /**
     * Cette function va servir à vérifier si la transaction s'est bien passer et mettre les infos de la commande en bdd
     *
     * @param Request $request
     * @param PaypalPaymentRepository $paypalPaymentRepository
     * @return void
     */
    public function completePurchaseAndSavePayment(Request $request, PaypalPaymentRepository $paypalPaymentRepository)
{
    $payerId = $request->query->get('PayerID');
    $paymentId = $request->query->get('paymentId');
    
    $response = $this->completePurchase($payerId, $paymentId);

    // On récupère les infos de la transaction
    $paymentData = $response->getData();

    if ($response->isSuccessful()) {
        $payment = new PaypalPayment();
        $payment->setPaymentId($paymentData['id'])
                ->setPayerId($paymentData['payer']['payer_info']['payer_id'])
                ->setPayerEmail($paymentData['payer']['payer_info']['email'])
                ->setAmount($paymentData['transactions'][0]['amount']['total'])
                ->setCurrency($_ENV['PAYPAL_CURRENCY'])
                ->setPurchasedAt(new \Datetime())
                ->setPaymentStatus($paymentData['state'])
        ;
        $paypalPaymentRepository->add($payment, true);          
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
 * Cette function va servir à vérifier si la commande PayPal n'est pas déjà en bdd
 *
 * @param PaypalPaymentRepository $paypalPaymentRepository
 * @param Request $request
 * @return boolean
 */
public function isExistingPaypalPayment(PaypalPaymentRepository $paypalPaymentRepository, Request $request): bool
{
    $existingPaypalPayment = $paypalPaymentRepository->findOneBy(['paymentId' => $request->query->get('paymentId')]);
    return $existingPaypalPayment !== null;
}
}