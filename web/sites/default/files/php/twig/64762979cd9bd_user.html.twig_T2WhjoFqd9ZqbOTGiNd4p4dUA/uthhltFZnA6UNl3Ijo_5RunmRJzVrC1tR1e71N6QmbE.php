<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* @webprofiler/Collector/user.html.twig */
class __TwigTemplate_8717eee05df921c191cdb5715b488756 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'toolbar' => [$this, 'block_toolbar'],
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_ad96c2d8979d8d23860453e7c5eb1520 = $this->extensions["Drupal\\tracer\\Twig\\Extension\\TraceableProfilerExtension"];
        $__internal_ad96c2d8979d8d23860453e7c5eb1520->enter($__internal_ad96c2d8979d8d23860453e7c5eb1520_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "@webprofiler/Collector/user.html.twig"));

        // line 1
        $this->displayBlock('toolbar', $context, $blocks);
        
        $__internal_ad96c2d8979d8d23860453e7c5eb1520->leave($__internal_ad96c2d8979d8d23860453e7c5eb1520_prof);

    }

    public function block_toolbar($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_ad96c2d8979d8d23860453e7c5eb1520 = $this->extensions["Drupal\\tracer\\Twig\\Extension\\TraceableProfilerExtension"];
        $__internal_ad96c2d8979d8d23860453e7c5eb1520->enter($__internal_ad96c2d8979d8d23860453e7c5eb1520_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "toolbar"));

        // line 2
        echo "
  ";
        // line 3
        $context["status"] = ((twig_get_attribute($this->env, $this->source, ($context["collector"] ?? null), "authenticated", [], "any", false, false, true, 3)) ? ("green") : ("red"));
        // line 4
        echo "
  ";
        // line 5
        ob_start(function () { return ''; });
        // line 6
        echo "    ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(twig_include($this->env, $context, "@webprofiler/Icon/user.svg"));
        echo "
    <span class=\"sf-toolbar-value\">";
        // line 7
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["collector"] ?? null), "username", [], "any", false, false, true, 7), 7, $this->source), "html", null, true);
        echo "</span>
  ";
        $context["icon"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 9
        echo "
  ";
        // line 10
        ob_start(function () { return ''; });
        // line 11
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["collector"] ?? null), "authenticated", [], "any", false, false, true, 11)) {
            // line 12
            echo "      <div class=\"sf-toolbar-info-piece\">
        <b>";
            // line 13
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Logged in as"));
            echo "</b>
        <span class=\"sf-toolbar-status sf-toolbar-status-";
            // line 14
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["status"] ?? null), 14, $this->source), "html", null, true);
            echo "\">";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["collector"] ?? null), "username", [], "any", false, false, true, 14), 14, $this->source), "html", null, true);
            echo "</span>
      </div>
      <div class=\"sf-toolbar-info-piece\">
        <b>";
            // line 17
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Roles"));
            echo "</b>
        <span>";
            // line 18
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, twig_join_filter($this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["collector"] ?? null), "roles", [], "any", false, false, true, 18), 18, $this->source), ", "), "html", null, true);
            echo "</span>
      </div>
      <div class=\"sf-toolbar-info-piece\">
        <b>";
            // line 21
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Authenticated by"));
            echo "</b>
        <span>";
            // line 22
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["collector"] ?? null), "provider", [], "any", false, false, true, 22), 22, $this->source), "html", null, true);
            echo "</span>
      </div>
    ";
        } else {
            // line 25
            echo "      <div class=\"sf-toolbar-info-piece\">
        <b>";
            // line 26
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["collector"] ?? null), "anonymous", [], "any", false, false, true, 26), 26, $this->source), "html", null, true);
            echo "</b>
      </div>
    ";
        }
        // line 29
        echo "  ";
        $context["text"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 30
        echo "
  ";
        // line 31
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(twig_include($this->env, $context, "@webprofiler/Profiler/toolbar_item.html.twig", ["link" => false, "status" => ((array_key_exists("status", $context)) ? (_twig_default_filter($this->sandbox->ensureToStringAllowed(($context["status"] ?? null), 31, $this->source), "")) : (""))]));
        echo "
";
        
        $__internal_ad96c2d8979d8d23860453e7c5eb1520->leave($__internal_ad96c2d8979d8d23860453e7c5eb1520_prof);

    }

    public function getTemplateName()
    {
        return "@webprofiler/Collector/user.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  134 => 31,  131 => 30,  128 => 29,  122 => 26,  119 => 25,  113 => 22,  109 => 21,  103 => 18,  99 => 17,  91 => 14,  87 => 13,  84 => 12,  81 => 11,  79 => 10,  76 => 9,  71 => 7,  66 => 6,  64 => 5,  61 => 4,  59 => 3,  56 => 2,  43 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "@webprofiler/Collector/user.html.twig", "/var/www/html/web/modules/contrib/webprofiler/templates/Collector/user.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("block" => 1, "set" => 3, "if" => 11);
        static $filters = array("escape" => 7, "t" => 13, "join" => 18, "default" => 31);
        static $functions = array("include" => 6);

        try {
            $this->sandbox->checkSecurity(
                ['block', 'set', 'if'],
                ['escape', 't', 'join', 'default'],
                ['include']
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
