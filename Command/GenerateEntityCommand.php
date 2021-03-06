<?php

/*
 * This file is part of the Nutellove project.
 *
 * Generate new Mootools Classes and associated Controllers and Routes
 * for an entity inside a bundle.
 *
 * ©author Antoine Goutenoir <antoine.goutenoir@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nutellove\JavascriptClassBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
//use Doctrine\ORM\EntityManager;


class GenerateEntityCommand extends AbstractCommand
{

  protected $_js_framework = 'Mootools';

  protected function configure()
  {
    $this
      ->setName('jsclass:generate:entity')
      ->setDescription('Generate javascript mootools classes providing xhr-managed persistence for an entity in a bundle from its yaml mapping.')
      ->addArgument('bundle', InputArgument::REQUIRED, 'The name of the bundle (case-sensitive).')
      ->addArgument('entity', InputArgument::REQUIRED, 'The name of the entity (case-sensitive).')
      ->addOption('mapping-type', null, InputOption::VALUE_OPTIONAL, 'The mapping type to to use for the entity. Can be yaml or annotation.', 'yaml')
      ->addOption('framework', null, InputOption::VALUE_OPTIONAL, 'The javascript framework for which your want to generate classes.', 'from_config')
      ->setHelp(<<<EOT
The <info>jsclass:generate:entity</info> task (re)generates a new Mootools Class Base entity, initializes if needed an extended Mootools Class entity in which you'll write your custom own javascript logic, and (re)generates the Controllers needed for PHP/JS synchronization via AJAX, all that inside a bundle :

  <info>./app/console jsclass:generate:entity MyBundle MyEntity</info>

EOT
    );
  }

  /**
   * @throws \InvalidArgumentException When the bundle doesn't end with Bundle (Example: "Bundle\MySampleBundle")
   * @FIXME
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Get the bundles we need
    $targetBundle = $this->getApplication()->getKernel()->getBundle($input->getArgument('bundle'));
    $javascriptClassBundle = $this->getApplication()->getKernel()->getBundle('JavascriptClassBundle');

    $entity = $input->getArgument('entity');
    $fullEntityClassName = $targetBundle->getNamespace().'\\Entity\\'.$entity;
    $mappingType = $input->getOption('mapping-type');
    $framework = ($input->getOption('framework') == 'from_config')
        ? $this->container->getParameter('nutellove_jsclass.generator.framework')
        : $input->getOption('framework');

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fetching the metadata for this Bundle/Entity/Mapping-Type
    $metadatas = $this->getBundleMetadatas($targetBundle);
    //var_dump ($metadatas);

    $classMetadata = $metadatas[$fullEntityClassName];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Setup a new exporter for the mapping type specified
    $cme = new ClassMetadataExporter();
    $exporter = $cme->getExporter($mappingType);

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Generation of the Base Mootools Entity
    $output->writeln(sprintf('Generating %s Javascript Entities for "<info>%s</info>"', $framework, $fullEntityClassName));

    $baseEntityPath = $targetBundle->getPath().'/Resources/public/jsclass/'.strtolower($this->getJsFramework()).
                      '/entity/'.strtolower($targetBundle->getName()).'/base/Base'.$entity.'.class.js';
    $baseEntityGeneratorGetter = 'get'.$framework.'BaseEntityGenerator';
    $baseEntityGenerator = call_user_func_array(array($this, $baseEntityGeneratorGetter), array());
    //$baseEntityGenerator = $this->getMootoolsBaseEntityGenerator();

    if ('annotation' === $mappingType) {
      $exporter->setEntityGenerator($baseEntityGenerator);
      $baseEntityCode = $exporter->exportClassMetadata($classMetadata);
      //$mappingPath = $mappingCode = false;
    } else {
      $baseEntityCode = $baseEntityGenerator->generateEntityClass($classMetadata);
    }

    $output->writeln(sprintf('  > Base Js Entity for into <info>%s</info>', $baseEntityPath));

    if (file_exists($baseEntityPath)) {
      $output->writeln(sprintf('    > Already existing, overwriting.'));
      //throw new \RuntimeException(sprintf("Mootools Base Entity %s already exists.", $classMetadata->name));
    }

    if (!is_dir($dir = dirname($baseEntityPath))) {
      mkdir($dir, 0777, true);
    }
    file_put_contents($baseEntityPath, $baseEntityCode);

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Generation (if needed) of the Mootools Entity
    $entityPath = $targetBundle->getPath().'/Resources/public/jsclass/'.strtolower($this->getJsFramework()).
                  '/entity/'.strtolower($targetBundle->getName()).'/'.$entity.'.class.js';

    $entityGenerator = $this->getMootoolsEntityGenerator();
    $entityGenerator->setClassToExtend ("Base".$targetBundle->getName().$entity);

    if ('annotation' === $mappingType) {
      $exporter->setEntityGenerator($entityGenerator);
      $entityCode = $exporter->exportClassMetadata($classMetadata);
      //$mappingPath = $mappingCode = false;
    } else {
      $entityCode = $entityGenerator->generateEntityClass($classMetadata);
    }

    $output->writeln(sprintf('  > Js Entity into <info>%s</info>', $entityPath));

    if (file_exists($entityPath)) {
      $output->writeln(sprintf('    > Already exists, left untouched'));
    } else {

      if (!is_dir($dir = dirname($entityPath))) {
          mkdir($dir, 0777, true);
      }
      file_put_contents($entityPath, $entityCode);

    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Generation of the Base Controller
    $output->writeln(sprintf('Generating Controllers for "<info>%s</info>"', $fullEntityClassName));

    $baseControllerPath = $javascriptClassBundle->getPath().'/Controller/Entity/'.$targetBundle->getName().'/Base/'.$entity.'Controller.php';

    $baseControllerGenerator = $this->getBaseControllerGenerator();

    if ('annotation' === $mappingType) {
      $exporter->setEntityGenerator($baseControllerGenerator);
      $baseControllerCode = $exporter->exportClassMetadata($classMetadata);
      //$mappingPath = $mappingCode = false;
    } else {
      $baseControllerCode = $baseControllerGenerator->generateEntityClass($classMetadata);
    }

    $output->writeln(sprintf('  > Base Entity Controller into <info>%s</info>', $baseControllerPath));

    if (file_exists($baseControllerPath)) {
      $output->writeln(sprintf('    > Already existing, overwriting.'));
    }

    if (!is_dir($dir = dirname($baseControllerPath))) {
      mkdir($dir, 0777, true);
    }
    file_put_contents($baseControllerPath, $baseControllerCode);

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Generation of the Controller (if needed)

    $controllerPath = $javascriptClassBundle->getPath().'/Controller/Entity/'.$targetBundle->getName().'/'.$entity.'Controller.php';

    $controllerGenerator = $this->getControllerGenerator();

    if ('annotation' === $mappingType) {
      $exporter->setEntityGenerator($controllerGenerator);
      $controllerCode = $exporter->exportClassMetadata($classMetadata);
    } else {
      $controllerCode = $controllerGenerator->generateEntityClass($classMetadata);
    }

    $output->writeln(sprintf('  > Entity Controller into <info>%s</info>', $controllerPath));

    if (file_exists($controllerPath)) {
      $output->writeln(sprintf('    > Already exists, left untouched'));
    } else {
      if (!is_dir($dir = dirname($controllerPath))) {
        mkdir($dir, 0777, true);
      }
      file_put_contents($controllerPath, $controllerCode);
    }

  }
}