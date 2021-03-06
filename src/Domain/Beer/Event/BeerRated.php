<?php

declare(strict_types=1);

namespace App\Domain\Beer\Event;

use App\Domain\Beer\Model\Connoisseur;
use App\Domain\Beer\Model\Id;
use App\Domain\Beer\Model\Rate;
use Prooph\EventSourcing\AggregateChanged;

final class BeerRated extends AggregateChanged
{
    public static function withData(Id $beerId, Connoisseur $connoisseurEmail, Rate $rate): self
    {
        return self::occur($beerId->value(), [
            'connoisseur_email' => $connoisseurEmail->value(),
            'rate' => $rate->value(),
        ]);
    }

    public function beerId(): Id
    {
        return new Id($this->aggregateId());
    }

    public function connoisseurEmail(): Connoisseur
    {
        return new Connoisseur($this->payload()['connoisseur_email']);
    }

    public function rate(): Rate
    {
        return new Rate($this->payload()['rate']);
    }
}
