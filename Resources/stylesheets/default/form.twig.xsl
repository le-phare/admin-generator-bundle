<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="text" encoding="utf-8" indent="no"/>

    <xsl:param name="ccname" select="php:function('Lephare\Bundle\AdminGeneratorBundle\Helper\Helper::getName', string(//entity/@name))"/>
    <xsl:param name="_name" select="php:function('Symfony\Component\DependencyInjection\Container::underscore', string($ccname))"/>

<!--  -->
<xsl:template match="//entity" mode="form.field.primary">
    <xsl:if test="//entity/id/@type != 'integer'">
    {{ form_row(form.<xsl:value-of select="//entity/id/@type"/>, { 'label': '<xsl:value-of select="$_name"/>.form.<xsl:value-of select="//entity/id/@type"/>.label' }) }}</xsl:if>
    <xsl:for-each select="//entity/field">
        <xsl:if test="contains('name title image subtitle slug position reference', @name)">
    {{ form_row(form.<xsl:value-of select="@name"/>, { 'label': '<xsl:value-of select="$_name"/>.form.<xsl:value-of select="@name"/>.label' }) }}</xsl:if>
    </xsl:for-each>
</xsl:template>

<!--  -->
<xsl:template match="//entity" mode="form.field.panel">
    <xsl:for-each select="//entity/many-to-one">
    {{ form_row(form.<xsl:value-of select="@field"/>, { 'label': '<xsl:value-of select="$_name"/>.form.<xsl:value-of select="@field"/>.label' }) }}</xsl:for-each>
    <xsl:for-each select="//entity/field">
        <xsl:if test="not(contains('name title image subtitle slug position reference createdAt updatedAt createdBy updatedBy publishStart publishEnd status', @name))">
    {{ form_row(form.<xsl:value-of select="@name"/>, { 'label': '<xsl:value-of select="$_name"/>.form.<xsl:value-of select="@name"/>.label' }) }}</xsl:if>
    </xsl:for-each>
</xsl:template>

<!--  -->
<xsl:template match="/">{% extends 'FarosAvantThemeBundle:CRUD:form.html.twig' %}

{% block primary_fields %}<xsl:apply-templates select="//entity" mode="form.field.primary"/>
{% endblock %}

{% block group_heading %}
&lt;li class="active"&gt;&lt;a href="#details" data-toggle="tab"&gt;&lt;i class="fa fa-wrench icon-highlight"&gt;&lt;/i&gt; {{ '<xsl:value-of select="$_name"/>.tab.details.label'|trans }}&lt;/a&gt;&lt;/li&gt;
{% endblock %}

{% block group_panel %}
&lt;div id="details" class="tab-pane active"&gt;<xsl:apply-templates select="//entity" mode="form.field.panel"/>
&lt;/div&gt;
{% endblock %}

{% block translations %}{% endblock %}
</xsl:template>
</xsl:stylesheet>
