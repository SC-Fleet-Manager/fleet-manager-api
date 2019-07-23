<?php

namespace App\Command;

use App\Repository\ShipRepository;
use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShipDiffCommand extends Command
{
    private $shipInfosProvider;
    private $shipRepository;

    public function __construct(
        ShipInfosProviderInterface $shipInfosProvider,
        ShipRepository $shipRepository
    ) {
        parent::__construct();
        $this->shipInfosProvider = $shipInfosProvider;
        $this->shipRepository = $shipRepository;
    }

    protected function configure(): void
    {
        $this->setName('app:ship-diff');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $theirShips = $this->shipInfosProvider->getAllShips();
        $ourShipNames = $this->shipRepository->distinctNames();

        $taggedShips = [];
        foreach ($ourShipNames as $ourShipName) {
            $taggedShips[] = [
                'name' => $ourShipName,
                'seen' => false,
            ];
        }

        foreach ($taggedShips as &$taggedShip) {
            $seen = false;
            foreach ($theirShips as $theirShip) {
                if ($theirShip->name === $taggedShip['name']) {
                    $seen = true;
                    break;
                }
            }
            $taggedShip['seen'] = $seen;
            if (!$seen) {
                // create a list of potentials ships that match
                $potentialMatches = [];
                $nameSanitized = preg_replace('/[^a-z0-9 ]+/i', '', $taggedShip['name']);
                $nameSanitized = preg_replace('/ +/i', ' ', $nameSanitized);
                $nameTokens = explode(' ', $nameSanitized);

                foreach ($nameTokens as $nameToken) {
                    foreach ($theirShips as $theirShip) {
                        if (stripos($theirShip->name, $nameToken) !== false
                            && !in_array($theirShip->name, $potentialMatches, true)) {
                            $potentialMatches[] = $theirShip->name;
                        }
                    }
                }
                $taggedShip['potentialMatches'] = $potentialMatches;
            }
        }

        $output->writeln(json_encode($taggedShips));
    }
}
