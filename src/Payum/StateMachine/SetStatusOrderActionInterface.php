<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\StateMachine;


interface SetStatusOrderActionInterface
{
    public function execute(array $order): void;
}
