<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Template\TemplateResolverInterface;
use Rector\Core\Template\TemplateTypeNotFound;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\FileSystemGuard;
use Symplify\SmartFileSystem\SmartFileSystem;

final class InitCommand extends Command
{
    /**
     * @var FileSystemGuard
     */
    private $fileSystemGuard;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var TemplateResolverInterface[]
     */
    private $templateResolvers;

    /**
     * @param TemplateResolverInterface[] $templateResolvers
     */
    public function __construct(
        FileSystemGuard $fileSystemGuard,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle,
        array $templateResolvers
    ) {
        $this->fileSystemGuard = $fileSystemGuard;
        $this->smartFileSystem = $smartFileSystem;
        $this->symfonyStyle = $symfonyStyle;
        $this->templateResolvers = $templateResolvers;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generate rector.php configuration file');
        $this->addOption(
            Option::TEMPLATE_TYPE,
            't',
            InputOption::VALUE_OPTIONAL,
            'A template type like default, nette, doctrine etc.',
            'default'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $templateType = (string) $input->getOption(Option::TEMPLATE_TYPE);

        $rectorTemplateFilePath = $this->resolveTemplateFilePathByType($templateType);

        $this->fileSystemGuard->ensureFileExists($rectorTemplateFilePath, __METHOD__);

        $rectorRootFilePath = getcwd() . '/rector.php';

        $doesFileExist = $this->smartFileSystem->exists($rectorRootFilePath);
        if ($doesFileExist) {
            $this->symfonyStyle->warning('Config file "rector.php" already exists');
        } else {
            $this->smartFileSystem->copy($rectorTemplateFilePath, $rectorRootFilePath);
            $this->symfonyStyle->success('"rector.php" config file was added');
        }

        return ShellCode::SUCCESS;
    }

    private function resolveTemplateFilePathByType(string $templateType): string
    {
        $rectorTemplateFilePath = null;

        foreach ($this->templateResolvers as $templateResolver) {
            if ($templateResolver->supports($templateType)) {
                $rectorTemplateFilePath = $templateResolver->provide();
                break;
            }
        }

        if ($rectorTemplateFilePath === null) {
            $availableTemplateTypes = implode(', ', $this->templateResolvers);
            throw TemplateTypeNotFound::typeNotFound($templateType, $availableTemplateTypes);
        }

        return $rectorTemplateFilePath;
    }
}
