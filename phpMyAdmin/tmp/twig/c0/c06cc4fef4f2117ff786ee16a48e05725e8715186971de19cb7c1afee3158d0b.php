<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* recent_favorite_table_no_tables.twig */
class __TwigTemplate_5a0dbb0123dcf8449069a7379c9481a73a7be7d02d5aa2928f5b54c461d754e5 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield "<li class=\"warp_link\">
    ";
        // line 2
        if (($context["is_recent"] ?? null)) {
            // line 3
            yield "        ";
yield _gettext("There are no recent tables.");
            // line 4
            yield "    ";
        } else {
            // line 5
            yield "        ";
yield _gettext("There are no favorite tables.");
            // line 6
            yield "    ";
        }
        // line 7
        yield "</li>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "recent_favorite_table_no_tables.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  55 => 7,  52 => 6,  49 => 5,  46 => 4,  43 => 3,  41 => 2,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "recent_favorite_table_no_tables.twig", "/home/cheru/public_html/phpMyAdmin/templates/recent_favorite_table_no_tables.twig");
    }
}
