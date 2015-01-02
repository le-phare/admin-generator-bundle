<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="text" encoding="utf-8" indent="no"/>

    <xsl:param name="ccname" select="php:function('Lephare\Bundle\AdminGeneratorBundle\Helper\Helper::getName', string(//entity/@name))"/>
    <xsl:param name="_name" select="php:function('Symfony\Component\DependencyInjection\Container::underscore', string($ccname))"/>
    <xsl:param name="namespace" select="php:function('sprintf', '%s\Form\%s', $bundle, php:function('Lephare\Bundle\AdminGeneratorBundle\Helper\Helper::getNamespace', string(//entity/@name)))"/>

<!--  -->
<xsl:template match="/" name="form.builder.add">
    <xsl:param name="name" />
    <xsl:param name="type" />
    <xsl:param name="length" />
    <xsl:choose>
        <xsl:when test="$type = 'string' and $length &gt; 200">
            ->add('<xsl:value-of select="$name"/>', 'ckeditor_light', [
                'required' => false,
            ])</xsl:when>
        <xsl:when test="$type = 'text'">
            ->add('<xsl:value-of select="$name"/>', 'ckeditor', [
                'required' => false,
                'transformers' => [ 'convert_images_link' ],
            ])</xsl:when>
        <xsl:otherwise>
            ->add('<xsl:value-of select="$name"/>')</xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!--  -->
<xsl:template match="//entity" mode="form.builder">
    <xsl:if test="//entity/id/@type != 'integer'">
            ->add('<xsl:value-of select="//entity/id/@name"/>')</xsl:if>
    <xsl:for-each select="//entity/field">
        <xsl:choose>
            <xsl:when test="contains('createdBy updatedBy createdAt updatedAt publishEnd', @name)"></xsl:when>
            <xsl:when test="@name = 'publishStart'">
            ->add('status')
            ->add('publicationDate', 'publication_date')</xsl:when>
            <xsl:when test="@name = 'status' and //entity/field/@name = 'publishStart'"></xsl:when>
            <xsl:otherwise>
            <xsl:call-template name="form.builder.add">
                <xsl:with-param name="name" select="@name" />
                <xsl:with-param name="type" select="@type" />
                <xsl:with-param name="length" select="@length" />
            </xsl:call-template></xsl:otherwise>
        </xsl:choose>
    </xsl:for-each>
    <xsl:for-each select="//entity/many-to-one">
        <xsl:choose>
            <xsl:when test="php:function('strpos', string(@target-entity), 'File')">
                <xsl:choose>
                    <xsl:when test="contains('image picture photo', @field)">
            ->add('<xsl:value-of select="@field"/>', 'imagepicker')</xsl:when>
                    <xsl:otherwise>
            ->add('<xsl:value-of select="@field"/>', 'filepicker')</xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
            ->add('<xsl:value-of select="@field"/>', 'entity', [
                'class' => '<xsl:value-of select="@target-entity"/>',
            ])</xsl:otherwise>
        </xsl:choose>
    </xsl:for-each>
</xsl:template>

<!--  -->
<xsl:template match="/">&lt;?php

namespace <xsl:value-of select="$namespace"/>;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class <xsl:value-of select="$ccname"/>Type extends AbstractType
{
    /**
     * @{inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder<xsl:apply-templates select="//entity" mode="form.builder"/>
        ;
    }

    /**
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => '<xsl:value-of select="//entity/@name"/>',
        ]);
    }

    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return '<xsl:value-of select="$_name"/>_type';
    }
}
</xsl:template>
</xsl:stylesheet>
