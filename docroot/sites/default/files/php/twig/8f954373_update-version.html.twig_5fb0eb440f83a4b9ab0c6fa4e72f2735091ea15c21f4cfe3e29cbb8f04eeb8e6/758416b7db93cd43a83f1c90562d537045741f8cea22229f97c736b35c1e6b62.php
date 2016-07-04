<?php

/* core/themes/stable/templates/admin/update-version.html.twig */
class __TwigTemplate_ddbd7edbe2ba69b57c8d795b2ed2f78e8469267c3ad952eb4f1c666f43b57386 extends Twig_Template
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
        $filters = array("without" => 16, "date" => 21, "t" => 26);
        $functions = array();

        try {
            $this->env->getExtension('sandbox')->checkSecurity(
                array(),
                array('without', 'date', 't'),
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

        // line 16
        echo "<div class=\"";
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "class", array()), "html", null, true));
        echo " project-update__version\"";
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, twig_without((isset($context["attributes"]) ? $context["attributes"] : null), "class"), "html", null, true));
        echo ">
  <div class=\"clearfix\">
    <div class=\"project-update__version-title layout-column layout-column--quarter\">";
        // line 18
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true));
        echo "</div>
    <div class=\"project-update__version-details layout-column layout-column--quarter\">
      <a href=\"";
        // line 20
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["version"]) ? $context["version"] : null), "release_link", array()), "html", null, true));
        echo "\">";
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["version"]) ? $context["version"] : null), "version", array()), "html", null, true));
        echo "</a>
      <span class=\"project-update__version-date\">(";
        // line 21
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["version"]) ? $context["version"] : null), "date", array()), "Y-M-d"), "html", null, true));
        echo ")</span>
    </div>
    <div class=\"layout-column layout-column--half\">
      <ul class=\"project-update__version-links\">
        <li class=\"project-update__download-link\">
          <a href=\"";
        // line 26
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["version"]) ? $context["version"] : null), "download_link", array()), "html", null, true));
        echo "\">";
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->renderVar(t("Download")));
        echo "</a>
        </li>
        <li class=\"project-update__release-notes-link\">
          <a href=\"";
        // line 29
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["version"]) ? $context["version"] : null), "release_link", array()), "html", null, true));
        echo "\">";
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->renderVar(t("Release notes")));
        echo "</a>
        </li>
      </ul>
    </div>
  </div>
</div>
";
    }

    public function getTemplateName()
    {
        return "core/themes/stable/templates/admin/update-version.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  78 => 29,  70 => 26,  62 => 21,  56 => 20,  51 => 18,  43 => 16,);
    }
}
/* {#*/
/* /***/
/*  * @file*/
/*  * Theme override for the version display of a project.*/
/*  **/
/*  * Available variables:*/
/*  * - attributes: HTML attributes suitable for a container element.*/
/*  * - title: The title of the project.*/
/*  * - version:  A list of data about the latest released version, containing:*/
/*  *   - version: The version number.*/
/*  *   - date: The date of the release.*/
/*  *   - download_link: The URL for the downloadable file.*/
/*  *   - release_link: The URL for the release notes.*/
/*  *//* */
/* #}*/
/* <div class="{{ attributes.class }} project-update__version"{{ attributes|without('class') }}>*/
/*   <div class="clearfix">*/
/*     <div class="project-update__version-title layout-column layout-column--quarter">{{ title }}</div>*/
/*     <div class="project-update__version-details layout-column layout-column--quarter">*/
/*       <a href="{{ version.release_link }}">{{ version.version }}</a>*/
/*       <span class="project-update__version-date">({{ version.date|date('Y-M-d') }})</span>*/
/*     </div>*/
/*     <div class="layout-column layout-column--half">*/
/*       <ul class="project-update__version-links">*/
/*         <li class="project-update__download-link">*/
/*           <a href="{{ version.download_link }}">{{ 'Download'|t }}</a>*/
/*         </li>*/
/*         <li class="project-update__release-notes-link">*/
/*           <a href="{{ version.release_link }}">{{ 'Release notes'|t }}</a>*/
/*         </li>*/
/*       </ul>*/
/*     </div>*/
/*   </div>*/
/* </div>*/
/* */
