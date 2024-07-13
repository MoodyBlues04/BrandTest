<?php

/**
 * Объект, валидирующий входные данные. Псевдокод основан на Request-ах в Laravel.
 * В исходном коде входные данные никак не валидировались, что является грубой ошибкой.
 */
class PayRequest extends Request
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|int|exists:users,id',
            'order_id' => 'required|int|exists:orders,id',
        ];
    }

    public function get(string $key, $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}

class Payment
{
    /**
     * payFromUserBalance - производит оплату существующего заказа с баланса пользователя
     * (если у него есть средства на внутреннем счете)
     *
     * При просмотре заказа, есть кнопка оплатить с внутреннего счета,
     * при нажатии на нее отправляется ajax запрос к данному методу (сам пользователь в личном кабинете нажимает эту кнопку),
     *
     * если вернулось true - пользователь видит что оплата успешная и получает сообщение в телеграм
     * если вернулось false - пользователь видит сообщение "Недостаточно средств на вашем счете"
     *
     * дополнительно:
     * User и Order - стандартные модели для работы с бд (внутрь них не надо проваливаться)
     * $request->get($key) - возвращает $_GET[$key]
     * NotificationSender - отправляет сообщения (в его реализацию тоже не проваливаемся)
     */
    public function payFromUserBalance(PayRequest $request): bool
    {
        $userId = $request->get('user_id');
        $orderId = $request->get('order_id');
//        передача с front-а 'sum' не имеет смысла, т к идет оплата заказа на конкретную сумму - необходимо передать только userId и orderId. закомменченным оставил только для пояснения
//        $sum = (float)$request->get('sum');

//        лучшим паттерном является использование DI репозиториев, а не статических методов (улучшает тестируемость и не нарушает DRY)
//        проверка на null === $user не требуется, т к производится в $request
        $user = User::getUserById($userId);
        $order = Order::getOrderById($orderId);

        if ($order->sum > $user->balance) {
            return false;
        }
        if (!$this->payForOrder($user, $order)) {
            return false;
        }

//        отправку сообщений лучше сделать через механизм 'events' + расположение нотификаций до сохранения в БД данных было ошибкой, т к сообщение об успешной оплате отправлялось до фактической оплаты
        (new NotificationSender())
            ->sendTelegramMessage($user->id, "Заказ #{$order->id} успешно оплачен!");

        return true;
    }

    /**
     * Основано на фасаде DB из Laravel.
     * Сохранение $order и $user должны быть транзакцией,
     * т к если при сохранении одного из них произойдет ошибка - это нарушит целостность данных
     */
    private function payForOrder(User $user, Order $order): bool
    {
        return DB::transaction(function () use ($user, $order) {
            $user->balance -= $order->sum;
            $order->status = Order::STATUS_PAID;
            return $user->save() && $order->save();
        });
    }
}
