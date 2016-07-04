<?php

/* core/modules/views/templates/views-view-rss.html.twig */
class __TwigTemplate_dc45828bb57ce72ecaf46749bae3882fb107df705b444a153d2dab4bbb0bc941 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array();
        $filters = array();
        $functions = array();

        try {
            $this->env->getExtension('sandbox')->checkSecurity(
                array(),
                array(),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setTemplateFile($this->getTemplateName());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 20
        echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<rss version=\"2.0\" xml:base=\"";
        // line 21
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["link"]) ? $context["link"] : null), "html", null, true));
        echo "\"";
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["namespaces"]) ? $context["namespaces"] : null), "html", null, true));
        echo ">
  <channel>
    <title>";
        // line 23
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true));
        echo "</title>
    <link>";
        // line 24
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["link"]) ? $context["link"] : null), "html", null, true));
        echo "</link>
    <description>";
        // line 25
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["description"]) ? $context["description"] : null), "html", null, true));
        echo "</description>
    <language>";
        // line 26
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["langcode"]) ? $context["langcode"] : null), "html", null, true));
        echo "</language>
    ";
        // line 27
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["channel_elements"]) ? $context["channel_elements"] : null), "html", null, true));
        echo "
    ";
        // line 28
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["items"]) ? $context["items"] : null), "html", null, true));
        echo "
  </channel>
</rss>
";
    }

    public function getTemplateName()
    {
        return "core/modules/views/templates/views-view-rss.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  73 => 28,  69 => 27,  65 => 26,  61 => 25,  57 => 24,  53 => 23,  46 => 21,  43 => 20,);
    }
}
/* {#*/
/* /***/
/*  * @file*/
/*  * Default template for feed displays that use the RSS style.*/
/*  **/
/*  * Available variables:*/
/*  * - link: The link to the feed (the view path).*/
/*  * - namespaces: The XML namespaces (added automatically).*/
/*  * - title: The title of the feed (as set in the view).*/
/*  * - description: The feed description (from feed settings).*/
/*  * - langcode: The language encoding.*/
/*  * - channel_elements: The formatted channel elements.*/
/*  * - items: The feed items themselves.*/
/*  **/
/*  * @see template_preprocess_views_view_rss()*/
/*  **/
/*  * @ingroup themeable*/
/*  *//* */
/* #}*/
/* <?xml version="1.0" encoding="utf-8" ?>*/
/* <rss version="2.0" xml:base="{{ link }}"{{ namespaces }}>*/
/*   <channel>*/
/*     <title>{{ title }}</title>*/
/*     <link>{{ link }}</link>*/
/*     <description>{{ description }}</description>*/
/*     <language>{{ langcode }}</language>*/
/*     {{ channel_elements }}*/
/*     {{ items }}*/
/*   </channel>*/
/* </rss>*/
/* */
