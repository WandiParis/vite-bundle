<?php

namespace Pentatrion\ViteBundle\Asset;

class EntrypointRenderer
{
  private $entrypointsLookup;
  private $tagRenderer;

  private $hasReturnedViteClient = false;

  public function __construct(EntrypointsLookup $entrypointsLookup)
  {
    $this->entrypointsLookup = $entrypointsLookup;
    $this->tagRenderer = new TagRenderer();
  }

  public function renderScripts(string $entryName, array $options = [])
  {
    $scriptTags = [];
    if (!$this->entrypointsLookup->isProd()) {
      $viteServer = $this->entrypointsLookup->getViteServer();

      if (!$this->hasReturnedViteClient) {
        $scriptTags[] = $this->tagRenderer->renderScriptFile($viteServer['origin'] . $viteServer['base'] . '@vite/client');
        if (isset($options['dependency']) && $options['dependency'] === 'react') {
          $scriptTags[] = $this->tagRenderer->renderReactRefreshInline($viteServer['origin'] . $viteServer['base']);
        }
        $this->hasReturnedViteClient = true;
      }
    }
    foreach ($this->entrypointsLookup->getJSFiles($entryName) as $fileName) {
      $scriptTags[] = $this->tagRenderer->renderScriptFile($fileName);
    }
    return implode('', $scriptTags);
  }

  public function renderLinks(string $entryName)
  {
    if (!$this->entrypointsLookup->isProd()) {
      return '';
    }

    $linkTags = [];
    foreach ($this->entrypointsLookup->getCSSFiles($entryName) as $fileName) {
      $linkTags[] = $this->tagRenderer->renderLinkStylesheet($fileName);
    }
    foreach ($this->entrypointsLookup->getJavascriptDependencies($entryName) as $fileName) {
      $linkTags[] = $this->tagRenderer->renderLinkPreload($fileName);
    }
    return implode('', $linkTags);
  }

    public function reset()
    {
        // resets the state of this service
        $hasReturnedViteClient = false;
    }
}
