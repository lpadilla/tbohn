<?php

/**
 * @file
 * Contains \Drupal\Console\Core\Generator\Generator.
 */

namespace Drupal\Console\Core\Generator;

use Drupal\Console\Core\Utils\TwigRenderer;
use Drupal\Console\Core\Utils\FileQueue;
use Drupal\Console\Core\Utils\CountCodeLines;

/**
 * Class Generator
 *
 * @package Drupal\Console\Core\Generator
 */
abstract class Generator
{
    /**
     * @var TwigRenderer
     */
    protected $renderer;

    /**
     * @var FileQueue
     */
    protected $fileQueue;

    /**
     * @var CountCodeLines
     */
    protected $countCodeLines;

    /**
     * @param $renderer
     */
    public function setRenderer(TwigRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param $fileQueue
     */
    public function setFileQueue(FileQueue $fileQueue)
    {
        $this->fileQueue = $fileQueue;
    }

    /**
     * @param $countCodeLines
     */
    public function setCountCodeLines(CountCodeLines $countCodeLines)
    {
        $this->countCodeLines = $countCodeLines;
    }

    /**
     * @param string $template
     * @param string $target
     * @param array  $parameters
     * @param null   $flag
     *
     * @return bool
     */
    protected function renderFile(
        $template,
        $target,
        $parameters = [],
        $flag = null
    ) {
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        $currentLine = 0;
        if (!empty($flag) && file_exists($target)) {
            $currentLine = count(file($target));
        }
        $content = $this->renderer->render($template, $parameters);

        if (file_put_contents($target, $content, $flag)) {
            $this->fileQueue->addFile($target);

            $newCodeLine = count(file($target));

            if ($currentLine > 0) {
                $newCodeLine = ($newCodeLine-$currentLine);
            }

            $this->countCodeLines->addCountCodeLines($newCodeLine);

            return true;
        }

        return false;
    }

    public function addSkeletonDir($skeletonDir)
    {
        $this->renderer->addSkeletonDir($skeletonDir);
    }
}
