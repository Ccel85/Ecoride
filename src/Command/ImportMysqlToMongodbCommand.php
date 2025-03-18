<?php

namespace App\Command;

use App\Entity\Covoiturage;
use App\Document\CovoiturageMongo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'import:mysql-to-mongodb',
    description: 'Add a short description for your command',
)]
class ImportMysqlToMongodbCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private DocumentManager $documentManager;

    public function __construct(EntityManagerInterface $entityManager, DocumentManager $documentManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->documentManager = $documentManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $covoituragesSQL = $this->entityManager->getRepository(Covoiturage::class)->findAll();

        foreach ($covoituragesSQL as $covoiturageSQL) {
            $covoiturageMongo = new CovoiturageMongo();
            $covoiturageMongo->setPrix($covoiturageSQL->getPrix());
            $covoiturageMongo->setDateDepart($covoiturageSQL->getDateDepart());
            $covoiturageMongo->setLieuDepart($covoiturageSQL->getLieuDepart());
            $covoiturageMongo->setLieuArrivee($covoiturageSQL->getLieuArrivee());
            $covoiturageMongo->setVoiture($covoiturageSQL->getVoiture()->getId());
            $covoiturageMongo->setConducteur($covoiturageSQL->getConducteur()?->getId());

            $this->documentManager->persist($covoiturageMongo);
        }

        $this->documentManager->flush();
        $output->writeln('✅ Importation réussie !');
        return Command::SUCCESS;
    }

   /*  protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    } */
}
