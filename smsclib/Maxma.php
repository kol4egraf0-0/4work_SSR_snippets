<?php
namespace Maxma;
require 'autoload.php';
use CloudLoyalty\Api\Client;
use CloudLoyalty\Api\Generated\Model\AdjustBalanceRequest;
use CloudLoyalty\Api\Generated\Model\AdjustBalanceRequestBalanceAdjustment;
use CloudLoyalty\Api\Generated\Model\AdjustBalanceResponse;
use CloudLoyalty\Api\Generated\Model\CalculationQuery;
use CloudLoyalty\Api\Generated\Model\CalculationQueryRow;
use CloudLoyalty\Api\Generated\Model\CalculationQueryRowProduct;
use CloudLoyalty\Api\Generated\Model\CancelOrderRequest;
use CloudLoyalty\Api\Generated\Model\ClientInfoReply;
use CloudLoyalty\Api\Generated\Model\ConfirmOrderRequest;
use CloudLoyalty\Api\Generated\Model\ConfirmOrderResponse;
use CloudLoyalty\Api\Generated\Model\ConfirmTicketRequest;
use CloudLoyalty\Api\Generated\Model\ClientInfoQuery;
use CloudLoyalty\Api\Generated\Model\ClientQuery;
use CloudLoyalty\Api\Generated\Model\GetBalanceResponse;
use CloudLoyalty\Api\Generated\Model\NewClientRequest;
use CloudLoyalty\Api\Generated\Model\NewClientResponse;
use CloudLoyalty\Api\Exception\TransportException;
use CloudLoyalty\Api\Exception\ProcessingException;
use CloudLoyalty\Api\Generated\Model\SendConfirmationCodeRequest;
use CloudLoyalty\Api\Generated\Model\SendConfirmationCodeResponse;
use CloudLoyalty\Api\Generated\Model\ShopQuery;
use CloudLoyalty\Api\Generated\Model\UpdateClientRequest;
use CloudLoyalty\Api\Generated\Model\V2CalculatePurchaseRequest;
use CloudLoyalty\Api\Generated\Model\V2SetOrderRequest;
use Exception;

class Maxma {
	/**
	 * Ключ
	 *
	 * @var string
	 */
	protected string $key;

	/**
	 * Ссылка на апи
	 *
	 * @var string
	 */
	protected string $api;

	/**
	 * Код торговой точки
	 *
	 * @var string
	 */
	protected string $code;

	/**
	 * Название торговой точки
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Название торговой точки
	 *
	 * @var float
	 */
	protected float $maxDiscount;

	/**
	 *
	 *
	 * @var Client
	 */
	protected Client $client;

	/**
	 *
	 *
	 * @var ClientQuery
	 */
	protected ClientQuery $clientQuery;

	/**
	 *
	 *
	 * @var ClientInfoQuery
	 */
	protected ClientInfoQuery $clientInfoQuery;

	/**
	 *
	 *
	 * @var UpdateClientRequest
	 */
	protected UpdateClientRequest $updateClientRequest;

	public bool $isSet;


	function __construct($clientPhone = false, $maxDiscount = 0, $shopCode = '', $shopName = '', $key = '', $api = '') {
		$this->key = empty($key) ? MAXMA_KEY : $key;
		$this->api = empty($api) ? MAXMA_API : $api;
		$this->code = empty($shopCode) ? MAXMA_CODE : $shopCode;
		$this->name = empty($shopName) ? MAXMA_NAME : $shopName;
		$this->maxDiscount = empty($maxDiscount) ? 0.2 : $maxDiscount;
		$this->client = new Client(['serverAddress' => $this->api, 'processingKey' => $this->key]);
		$this->clientQuery = new ClientQuery();
		$this->clientInfoQuery = new clientInfoQuery();
		$this->updateClientRequest = new updateClientRequest();
		if (!empty($clientPhone)) {
			$this->clientQuery->setPhoneNumber($clientPhone);
			$this->isSet = true;
		}
		else {
			$this->isSet = false;
		}
	}

	/**
	 * @param $clientInfo
	 *
	 */
	public function setClientInfoQuery($clientInfo): void {
		if (!empty($clientInfo['phone'])) {
			$this->clientInfoQuery->setPhoneNumber($clientInfo['phone']);
		}
		if (!empty($clientInfo['name'])) {
			$this->clientInfoQuery->setName($clientInfo['name']);
		}
		if (!empty($clientInfo['email'])) {
			$this->clientInfoQuery->setEmail($clientInfo['email']);
		}
		if (!empty($clientInfo['gender'])) {
			$this->clientInfoQuery->setGender($clientInfo['gender']);
		}
		if (!empty($clientInfo['birthday'])) {
			$datetime = new \DateTime($clientInfo['birthday']);
			$this->clientInfoQuery->setBirthdate($datetime);
		}
		if (!empty($clientInfo['card'])) {
			$this->clientInfoQuery->setCard($clientInfo['card']);
		}
		if (!empty($clientInfo['sub_email'])) {
			$this->clientInfoQuery->setIsEmailSubscribed($clientInfo['subs_email']);
		}
		if (!empty($clientInfo['sub_phone'])) {
			$this->clientInfoQuery->setIsPhoneSubscribed($clientInfo['sub_phone']);
		}
		if (!empty($clientInfo['id'])) {
			$this->clientInfoQuery->setExternalId($clientInfo['id']);
		}
		if (!empty($clientInfo['level'])) {
			$this->clientInfoQuery->setLevel($clientInfo['level']);
		}
		if (!empty($clientInfo['extra'])) {
			$this->clientInfoQuery->setExtraFields($clientInfo['level']);
		}
		if (!empty($clientInfo['children'])) {
			$this->clientInfoQuery->setChildren($clientInfo['children']);
		}
	}


	/**
	 * @param $clientInfo
	 * @param bool $promoCode
	 *
	 * @return bool|ClientInfoReply
	 */
	public function createClient($clientInfo, $promoCode = false) {
		// clear previous info
		$this->clientQuery = new ClientQuery();
		$this->clientInfoQuery = new clientInfoQuery();
		$this->updateClientRequest = new updateClientRequest();

		$this->setClientInfoQuery($clientInfo);
		$newClientRequest = new NewClientRequest();
		$newClientRequest->setClient($this->clientInfoQuery);
		if (!empty($promoCode)) {
			$newClientRequest->setPromocode($promoCode);
		}
		try {
			$client = $this->client->newClient($newClientRequest);
			$client = $client->getClient();
			$clientInfo = [
				'id' => $client->getExternalId(),
				'phone' => $client->getPhoneNumber(),
				'card' => $client->getCardString(),
			];
			if (!empty($clientInfo['id'])) {
				$this->clientQuery->setExternalId($clientInfo['id']);
			}
			if (!empty($clientInfo['phone'])) {
				$this->clientQuery->setPhoneNumber($clientInfo['phone']);
			}
			if (!empty($clientInfo['card'])) {
				$this->clientQuery->setCard($clientInfo['card']);
			}

			$shopQuery = new ShopQuery();
			$shopQuery->setCode('222');
			$shopQuery->setName('Resto');
			$newClientRequest->setShop($shopQuery);

			return true;
		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка создания клиента', 'createClient', $e->getMessage(), $e->getHint().' [код #'.$e->getCode().']'], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'createClient', $e->getMessage()], 'zError');
			return false;
		}
	}

	public function initClientByExtId($extId) {
		$this->clientQuery->setExternalId($extId);
		return $this;
	}

	public function initClientByPhone($phone) {
		$this->clientQuery->setPhoneNumber($phone);
		return $this;
	}

	public function initClientByCard($card) {
		$this->clientQuery->setCard($card);
		return $this;
	}


	/**
	 * @param $amount
	 *
	 * @param bool $days
	 * @param bool $comment
	 * @param bool $notify
	 *
	 * @return bool|AdjustBalanceResponse
	 */
	public function changeBonuses($amount, $days = false, $comment = false, $notify = false) {
		$adjustBalanceRequest = new AdjustBalanceRequest();
		$adjustBalanceRequestData = new AdjustBalanceRequestBalanceAdjustment();
		$adjustBalanceRequestData->setAmountDelta($amount);
		if ($days) {
			$adjustBalanceRequestData->setExpirationPeriodDays($days);
		}
		if ($comment) {
			$adjustBalanceRequestData->setComment($comment);
		}
		if ($notify) {
			$adjustBalanceRequestData->setNotify($notify);
		}
		$adjustBalanceRequest->setClient($this->clientQuery);
		$adjustBalanceRequest->setBalanceAdjustment($adjustBalanceRequestData);
		try {
			return $this->client->adjustBalance($adjustBalanceRequest);
		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка изменения баланса', 'changeBonuses', $e->getMessage(), $e->getHint()], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'changeBonuses', $e->getMessage()], 'zError');
			return false;
		}
	}

	/**
	 * gender: 0 — пол неизвестен, 1 — мужской, 2 — женский
	 *
	 * @param $clientInfo
	 *
	 * @return bool|NewClientResponse
	 */
	public function updateClient($clientInfo) {
		//todo chtw
		$this->setClientInfoQuery($clientInfo);
		$this->updateClientRequest->setClient($this->clientInfoQuery);

		if ($this->clientQuery->getExternalId()) {
			$this->updateClientRequest->setExternalId($this->clientQuery->getExternalId());
		}
		if ($this->clientQuery->getCard()) {
			$this->updateClientRequest->setCard($this->clientQuery->getCard());
		}
		if ($this->clientQuery->getPhoneNumber()) {
			$this->updateClientRequest->setPhoneNumber($this->clientQuery->getPhoneNumber());
		}

		try {
			return $this->client->updateClient($this->updateClientRequest);
		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка обновления клиента', 'updateClient', $e->getMessage(), $e->getHint()], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'updateClient', $e->getMessage()], 'zError');
			return false;
		}
	}

	/**
	 * gender: 0 — пол неизвестен, 1 — мужской, 2 — женский
	 *
	 * @param bool $isAnon
	 * @param bool|string $phone
	 *
	 * @return bool|SendConfirmationCodeResponse
	 */

	public function sendCode($isAnon = false, $phone = false) {
		//todo chtw

		try {
			$nccr = new SendConfirmationCodeRequest();
			if ($this->clientQuery->getExternalId()) {
				$nccr->setExternalId($this->clientQuery->getExternalId());
			}
			if ($this->clientQuery->getCard()) {
				$nccr->setCard($this->clientQuery->getCard());
			}
			if ($this->clientQuery->getPhoneNumber()) {
				$nccr->setPhoneNumber($this->clientQuery->getPhoneNumber());
			}
			if ($isAnon) {
				$nccr->setIsAnonymousClient( true );
				$nccr->setTo($phone);
			}

			return $this->client->sendConfirmationCode($nccr);
		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка получения клиента', 'sendCode', $e->getMessage(), $e->getHint()], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'sendCode', $e->getMessage()], 'zError');
			return false;
		}

	}

	public function checkClient($phone) {
		try {
			$clientQuery = new ClientQuery();
			$clientQuery->setPhoneNumber( $phone );
			$balance = $this->client->getBalance( $clientQuery );
			if ($balance) {
				return ['error' => 'client_exist', 'text' => ''];
			}
			return ['error' => 'other', 'text' => 'Неизвестная ошибка.'];
		}
		catch (TransportException | ProcessingException $e) {
			if ($e->getCode() == 3) {
				return ['error' => false, 'text' => $e->getMessage()];
			}
			if ($e->getCode() == 20) {
				return ['error' => 'invalid_phone', 'text' => $e->getMessage()];
			}
			chtwDebugRows(['Ошибка получения клиента', 'checkClient', $e->getMessage(), $e->getHint(), $e->getCode()], 'zError');
			return ['error' => 'other', 'text' => $e->getMessage()];
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'checkClient', $e->getMessage()], 'zError');
			return ['error' => 'other', 'text' => $e->getMessage()];
		}
	}

	public function getClient($formatted = false, $phone = '') {
		try {
			if (!empty($phone)) {
				$cc = new ClientQuery();
				$cc->setPhoneNumber($phone);
				$balance = $this->client->getBalance($cc);
			}
			else {
				$balance = $this->client->getBalance($this->clientQuery);
			}
			if ($formatted) {
				$clientInfo['level'] = [];
				$clientInfo['bonuses'] = [];
				if ($lvl = $balance->getLevel()) {
					$clientInfo['level']['level'] = $lvl->getLevel();
					$clientInfo['level']['name'] = $lvl->getName();
					$clientInfo['level']['remain'] = $lvl->getRemaining();
					$clientInfo['level']['spent'] = $lvl->getLevelSpent();
				}
				if ($cln = $balance->getClient()) {
					$clientInfo['bonuses']['all'] = $cln->getBonuses();
				}
				$clientInfo['bonuses']['list'] = $balance->getBonuses();
				return $clientInfo;
			}
			return $balance;
		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка получения клиента', 'getClient', $e->getMessage(), $e->getHint()], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'getClient', $e->getMessage()], 'zError');
			return false;
		}
	}

	/**
	 * @param $purchase
	 *
	 * @return CalculationQuery
	 */
	public function getCalculationQuery($purchase) {
		$calcQuery = new CalculationQuery();
		$calcQuery->setClient($this->clientQuery);

		$shopQuery = new ShopQuery();
		$shopQuery->setCode($this->code);
		$shopQuery->setName($this->name);

		$calcQuery->setShop($shopQuery);
		$rows = [];

		foreach ($purchase['products'] as $product) {
			$calcQueryRow = new CalculationQueryRow();

			$calcQueryRowProduct = new CalculationQueryRowProduct();
			$calcQueryRowProduct->setSku($product['sku']);
			$calcQueryRowProduct->setBlackPrice($product['price']);
			if ($product['title']) {
				$calcQueryRowProduct->setTitle($product['title']);
			}

			if (!empty($product['promocode'])) {
				$calcQuery->setPromocode($purchase['promocode']);
			}
			if ($product['no_bonus']) {
				$calcQueryRow->setNoCollectBonuses(true);
			}
			$calcQueryRow->setProduct($calcQueryRowProduct);
			$calcQueryRow->setQty($product['qty']);
			$calcQueryRow->setMaxDiscount($product['no_discount'] ? 0 : ($product['price'] * $product['qty'] * $this->maxDiscount));
			$rows[] = $calcQueryRow;
		}

		$calcQuery->setRows($rows);

		if ($purchase['applyBonuses']) {
			$calcQuery->setApplyBonuses($purchase['applyBonuses']);
		}
		if ($purchase['promocode']) {
			$calcQuery->setPromocode($purchase['promocode']);
		}

		return $calcQuery;
	}

	public function calculatePurchase($purchase) {
		$calcQueryRequest = new V2CalculatePurchaseRequest();
		$calcQuery = $this->getCalculationQuery($purchase);
		$calcQueryRequest->setCalculationQuery($calcQuery);

		try {
			$result = $this->client->calculatePurchase($calcQueryRequest);

		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка расчета покупки', 'calculatePurchase', $e->getMessage(), $e->getHint()], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'calculatePurchase', $e->getMessage()], 'zError');
			return false;
		}

		return $result;

	}

	public function createOrder($purchase, $order_id) {
		$calcQueryRequest = new V2SetOrderRequest();
		$calcQueryRequest->setOrderId($order_id);
		$calcQuery = $this->getCalculationQuery($purchase);
		$calcQueryRequest->setCalculationQuery($calcQuery);
		try {
			$result = $this->client->setOrder($calcQueryRequest);
		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка создания заказа', 'createOrder', $e->getMessage(), $e->getHint()], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'createOrder', $e->getMessage()], 'zError');
			return false;
		}

		return $result;
	}

	/**
	 * @param $order_id
	 *
	 * @return bool|ConfirmOrderResponse
	 */
	public function confirmOrder($order_id) {
		$confirmOrderRequest = new ConfirmOrderRequest();
		$confirmOrderRequest->setOrderId((string)$order_id);
		try {
			$result = $this->client->confirmOrder($confirmOrderRequest);
		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка подтверждения заказа', 'confirmOrder', $e->getMessage(), $e->getHint()], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'confirmOrder', $e->getMessage()], 'zError');
			return false;
		}

		return $result;
	}


	public function cancelOrder($orderID) {
		$cancelOrderRequest = new CancelOrderRequest();
		$cancelOrderRequest->setOrderId($orderID);
		try {
			return $this->client->cancelOrder($cancelOrderRequest);
		}
		catch (TransportException | ProcessingException $e) {
			chtwDebugRows(['Ошибка отмена заказа', 'cancelOrder', $e->getMessage()], 'zError');
			return false;
		}
		catch (Exception $e) {
			chtwDebugRows(['Ошибка общая', 'cancelOrder', $e->getMessage()], 'zError');
			return false;
		}
	}


}