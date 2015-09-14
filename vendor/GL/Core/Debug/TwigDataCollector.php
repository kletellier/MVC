<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GL\Core\Debug;
 
use Twig_Environment;
use GL\Core\Twig\TwigArrayDumper;

/**
 * Collects info about the current request
 */
class TwigDataCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable,  \DebugBar\DataCollector\AssetProvider
{
    protected $_profiler = null;

     public function __construct($profiler)
    {
        $this->_profile = $profiler;
    }

    public function collect()
    {
        $templates = array();
        $accuRenderTime = 0;

        // retrieve profiler
        $profile = $this->_profile;

        // extract data from profiler
        $dumper = new TwigArrayDumper;
        $dump = $dumper->dump($profile);
 
        foreach ($dump as $tpl) {
            $accuRenderTime += $tpl['duration'] ;
            $templates[] = array(
                'name' => $tpl['template'],
                'render_time' => $tpl['duration'] ,
                'render_time_str' => $this->formatDuration($tpl['duration'] )
            );
        }

        return array(
            'nb_templates' => count($templates),
            'templates' => $templates,
            'accumulated_render_time' => $accuRenderTime,
            'accumulated_render_time_str' => $this->formatDuration($accuRenderTime)
        );
    }

    public function getName()
    {
        return 'twig';
    }

    public function getWidgets()
    {
        return array(
            'twig' => array(
                'icon' => 'leaf',
                'widget' => 'PhpDebugBar.Widgets.TemplatesWidget',
                'map' => 'twig',
                'default' => '[]'
            ),
            'twig:badge' => array(
                'map' => 'twig.nb_templates',
                'default' => 0
            )
        );
    }

    public function getAssets()
    {
        return array(
            'css' => 'widgets/templates/widget.css',
            'js' => 'widgets/templates/widget.js'
        );
    }
}
