<?php

namespace App\Command;

use App\Entity\Department;
use App\Repository\DepartmentRepository;
use App\Service\HttpClientConnector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;


#[AsCommand(
    name: 'app:departement',
    description: 'Add a short description for your command',
)]
class DepartementCommand extends Command
{
    public function __construct(
        private HttpClientConnector $httpClient,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }
    protected function configure(): void
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
      
        $query = $this->httpClient->urlConnect("https://geo.api.gouv.fr/departements/");

        $json = json_decode($query->getContent(), true);

        // $form = $this->createForm(AccountRegisterType::class, )

        foreach ($json as $department) {
            $newDepartment = new Department();
            $newDepartment->setName($department['nom']);
            $newDepartment->setCode($department['code']);
            $this->entityManager->persist($newDepartment);
            // dump($newDepartment);
        }
        $this->entityManager->flush();

        $io->success('Les departements ont été ajoutés');

        return Command::SUCCESS;
    }
}
