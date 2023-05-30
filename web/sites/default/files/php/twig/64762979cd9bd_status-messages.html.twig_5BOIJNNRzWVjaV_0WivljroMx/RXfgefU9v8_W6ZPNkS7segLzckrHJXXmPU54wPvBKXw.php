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

/* core/themes/olivero/templates/misc/status-messages.html.twig */
class __TwigTemplate_a5c4c6ae6f5b1a592e49076e47925e8f extends Template
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
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_ad96c2d8979d8d23860453e7c5eb1520 = $this->extensions["Drupal\\tracer\\Twig\\Extension\\TraceableProfilerExtension"];
        $__internal_ad96c2d8979d8d23860453e7c5eb1520->enter($__internal_ad96c2d8979d8d23860453e7c5eb1520_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "core/themes/olivero/templates/misc/status-messages.html.twig"));

        // line 22
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("olivero/messages"), "html", null, true);
        echo "

<div data-drupal-messages class=\"messages-list\">
  <div class=\"messages__wrapper layout-container\">
    ";
        // line 26
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["message_list"] ?? null));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["type"] => $context["messages"]) {
            // line 27
            echo "      ";
            // line 28
            $context["classes"] = [0 => "messages-list__item", 1 => "messages", 2 => ("messages--" . $this->sandbox->ensureToStringAllowed(            // line 31
$context["type"], 31, $this->source))];
            // line 34
            echo "
      <div";
            // line 35
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [0 =>             // line 36
($context["classes"] ?? null)], "method", false, false, true, 35), "setAttribute", [0 => "data-drupal-selector", 1 => "messages"], "method", false, false, true, 36), "setAttribute", [0 => "role", 1 => "contentinfo"], "method", false, false, true, 37), "setAttribute", [0 => "aria-label", 1 => (($__internal_compile_0 =             // line 39
($context["status_headings"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0[$context["type"]] ?? null) : null)], "method", false, false, true, 38), 39, $this->source), "html", null, true);
            // line 40
            echo ">
        <div class=\"messages__container\" data-drupal-selector=\"messages-container\"";
            // line 41
            if (($context["type"] == "error")) {
                echo " role=\"alert\"";
            }
            echo ">
          ";
            // line 42
            if ((($__internal_compile_1 = ($context["status_headings"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1[$context["type"]] ?? null) : null)) {
                // line 43
                echo "            <div class=\"messages__header\">
            <h2 class=\"visually-hidden\">";
                // line 44
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_2 = ($context["status_headings"] ?? null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2[$context["type"]] ?? null) : null), 44, $this->source), "html", null, true);
                echo "</h2>
              <div class=\"messages__icon\">
                ";
                // line 46
                if (($context["type"] == "error")) {
                    // line 47
                    echo "                  ";
                    $this->loadTemplate("@olivero/../images/error.svg", "core/themes/olivero/templates/misc/status-messages.html.twig", 47)->display($context);
                    // line 48
                    echo "                ";
                } elseif (($context["type"] == "warning")) {
                    // line 49
                    echo "                  ";
                    $this->loadTemplate("@olivero/../images/warning.svg", "core/themes/olivero/templates/misc/status-messages.html.twig", 49)->display($context);
                    // line 50
                    echo "                ";
                } elseif (($context["type"] == "status")) {
                    // line 51
                    echo "                  ";
                    $this->loadTemplate("@olivero/../images/status.svg", "core/themes/olivero/templates/misc/status-messages.html.twig", 51)->display($context);
                    // line 52
                    echo "                ";
                } elseif (($context["type"] == "info")) {
                    // line 53
                    echo "                  ";
                    $this->loadTemplate("@olivero/../images/info.svg", "core/themes/olivero/templates/misc/status-messages.html.twig", 53)->display($context);
                    // line 54
                    echo "                ";
                }
                // line 55
                echo "              </div>
            </div>
          ";
            }
            // line 58
            echo "          <div class=\"messages__content\">
            ";
            // line 59
            if ((twig_length_filter($this->env, $context["messages"]) > 1)) {
                // line 60
                echo "              <ul class=\"messages__list\">
                ";
                // line 61
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($context["messages"]);
                foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
                    // line 62
                    echo "                  <li class=\"messages__item\">";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($context["message"], 62, $this->source), "html", null, true);
                    echo "</li>
                ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['message'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 64
                echo "              </ul>
            ";
            } else {
                // line 66
                echo "              ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, twig_first($this->env, $this->sandbox->ensureToStringAllowed($context["messages"], 66, $this->source)), "html", null, true);
                echo "
            ";
            }
            // line 68
            echo "          </div>
        </div>
      </div>
      ";
            // line 72
            echo "      ";
            $context["attributes"] = twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "removeClass", [0 => ($context["classes"] ?? null)], "method", false, false, true, 72);
            // line 73
            echo "    ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['type'], $context['messages'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 74
        echo "  </div>
</div>
";
        
        $__internal_ad96c2d8979d8d23860453e7c5eb1520->leave($__internal_ad96c2d8979d8d23860453e7c5eb1520_prof);

    }

    public function getTemplateName()
    {
        return "core/themes/olivero/templates/misc/status-messages.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  181 => 74,  167 => 73,  164 => 72,  159 => 68,  153 => 66,  149 => 64,  140 => 62,  136 => 61,  133 => 60,  131 => 59,  128 => 58,  123 => 55,  120 => 54,  117 => 53,  114 => 52,  111 => 51,  108 => 50,  105 => 49,  102 => 48,  99 => 47,  97 => 46,  92 => 44,  89 => 43,  87 => 42,  81 => 41,  78 => 40,  76 => 39,  75 => 36,  74 => 35,  71 => 34,  69 => 31,  68 => 28,  66 => 27,  49 => 26,  42 => 22,);
    }

    public function getSourceContext()
    {
        return new Source("", "core/themes/olivero/templates/misc/status-messages.html.twig", "/var/www/html/web/core/themes/olivero/templates/misc/status-messages.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("for" => 26, "set" => 28, "if" => 41, "include" => 47);
        static $filters = array("escape" => 22, "length" => 59, "first" => 66);
        static $functions = array("attach_library" => 22);

        try {
            $this->sandbox->checkSecurity(
                ['for', 'set', 'if', 'include'],
                ['escape', 'length', 'first'],
                ['attach_library']
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
