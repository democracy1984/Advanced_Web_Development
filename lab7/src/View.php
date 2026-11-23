<?php

declare(strict_types=1);

/**
 * Рендеринг шаблонов: нативный PHP или Twig.
 */
final class View
{
    public function __construct(
        private string $engine,
        private string $templateDir,
        private ?\Twig\Environment $twig = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data): string
    {
        if ($this->engine === 'twig') {
            if ($this->twig === null) {
                throw new RuntimeException('Twig не инициализирован.');
            }

            return $this->twig->render($template . '.twig', $data);
        }

        $data['template'] = $template;
        extract($data, EXTR_SKIP);

        ob_start();
        require $this->templateDir . '/native/layout.php';

        return (string) ob_get_clean();
    }
}
