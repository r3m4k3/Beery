<?php

declare(strict_types=1);

namespace Tests\Behat\Context\Application;

use App\Application\Command\AddBeer;
use App\Domain\Beer\Event\BeerAdded;
use App\Domain\Beer\Model\Abv;
use App\Domain\Beer\Model\Id;
use App\Domain\Beer\Model\Name;
use App\Infrastructure\Generator\UuidGeneratorInterface;
use Behat\Behat\Context\Context;
use Prooph\ServiceBus\CommandBus;
use Tests\Service\Prooph\Plugin\EventsRecorder;
use Webmozart\Assert\Assert;

final class BeerContext implements Context
{
    /** @var CommandBus */
    private $commandBus;

    /** @var EventsRecorder */
    private $eventsRecorder;

    /** @var UuidGeneratorInterface */
    private $generator;

    public function __construct(
        CommandBus $commandBus,
        EventsRecorder $eventsRecorder,
        UuidGeneratorInterface $generator
    ) {
        $this->commandBus = $commandBus;
        $this->eventsRecorder = $eventsRecorder;
        $this->generator = $generator;
    }

    /**
     * @When I add a new :beerName beer which has :abv% ABV
     */
    public function iAddANewBeerWhichHasAbv(string $beerName, int $abv): void
    {
        $this->commandBus->dispatch(
            AddBeer::create(new Id($this->generator->generate()), new Name($beerName), new Abv($abv))
        );
    }

    /**
     * @Then the :beerName beer should be available in the catalogue
     */
    public function theBeerShouldBeAvailableInTheCatalogue(string $beerName): void
    {
        $message = $this->eventsRecorder->getLastMessage();

        $event = $message->event();
        \assert($event instanceof BeerAdded, sprintf(
            'Event has to be of class %s, but %s given',
            BeerAdded::class,
            get_class($event)
        ));
        Assert::eq($event->name(), new Name($beerName));
    }
}
