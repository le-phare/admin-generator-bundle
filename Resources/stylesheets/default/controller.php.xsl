<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="text" encoding="utf-8" indent="no"/>

    <xsl:param name="ccname" select="php:function('Lephare\Bundle\AdminGeneratorBundle\Helper\Helper::getName', string(//entity/@name))"/>
    <xsl:param name="_name" select="php:function('Symfony\Component\DependencyInjection\Container::underscore', string($ccname))"/>
    <xsl:param name="namespace" select="php:function('sprintf', '%s\Controller\%s', $bundle, php:function('Lephare\Bundle\AdminGeneratorBundle\Helper\Helper::getNamespace', string(//entity/@name)))"/>
    <xsl:param name="routeprefix" select="php:function('Lephare\Bundle\AdminGeneratorBundle\Helper\Helper::getRoutePrefix', string($bundle))"/>

<!--  -->
<xsl:template match="//entity" mode="column">
        <xsl:if test="//entity/id/@name = 'id'">
            [
                'data' => 'id',
                'label' => '<xsl:value-of select="$_name"/>.list.column.id',
                'field' => '<xsl:value-of select="$_name"/>.id',
                'class' => 'row-id',
                'process' => function ($data) { return $data->getId(); },
            ],</xsl:if>
        <xsl:for-each select="//entity/field">
        <xsl:if test="@name = 'name'">
            [
                'data' => 'name',
                'label' => '<xsl:value-of select="$_name"/>.list.column.name',
                'field' => '<xsl:value-of select="$_name"/>.name',
                'searchable' => true,
            ],</xsl:if>
        <xsl:if test="@name = 'publishStart'">
            [
                'data' => 'status',
                'process' => function ($data) use ($translator) {
                    return $translator->trans($data->getStatusLabel());
                },
            ],
            [
                'data' => 'publishStart',
                'process' => function ($data) use ($dateProcessor) {
                    return $dateProcessor($data->getPublishStart());
                },
            ],
            [
                'data' => 'publishEnd',
                'process' => function ($data) use ($translator, $dateProcessor) {
                    return $dateProcessor($data->getPublishEnd()) ?: $translator->trans('publishable.unlimited');
                },
            ],</xsl:if>
        </xsl:for-each>
</xsl:template>

<!--  -->
<xsl:template match="//entity" mode="column.dateProcessor">
    <xsl:if test="field/@type = 'datetime'">
        $formatter = \IntlDateFormatter::create(null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);

        $dateProcessor = function ($date) use ($formatter) {
            return null === $date ? null : $formatter->format($date);
        };
    </xsl:if>
</xsl:template>

<!--  -->
<xsl:template match="//entity/field" mode="filters.query">
    <xsl:if test="@name = 'publishStart'">
        if (isset($filters['status'])) {
            $queryBuilder
                ->andWhere('<xsl:value-of select="$_name"/>.status = :status')
                ->setParameter('status', $filters['status'])
            ;
        }
    </xsl:if>
</xsl:template>

<!--  -->
<xsl:template match="//entity/field" mode="filters.form.builder">
        <xsl:if test="@name = 'publishStart'">
            ->add('status', 'choice', [
                'choices' => $refl->getStaticPropertyValue('statusValues'),
                'required' => false,
                'label' => '<xsl:value-of select="$_name"/>.list.filters.status',
            ])
        </xsl:if>
</xsl:template>

<!--  -->
<xsl:template match="//entity/field" mode="filters.form">
    <xsl:if test="@name = 'publishStart'">

    /**
     * @{inheritDoc}
     */
    public function getFilterForm(Request $request)
    {
        $builder = $this->createFormFilterBuilder($request);

        $refl = new \ReflectionClass($this->getEntityName());

        $builder<xsl:apply-templates select="//entity/field" mode="filters.form.builder"/>;

        return $builder->getForm();
    }</xsl:if>
</xsl:template>

<!--  -->
<xsl:template match="/">&lt;?php

namespace <xsl:value-of select="$namespace"/>;

use Faros\AdminBundle\Controller\CRUD\ORMController;
use Symfony\Component\HttpFoundation\Request;
use <xsl:value-of select="php:function('str_replace', 'Controller', 'Form', $namespace)"/>\<xsl:value-of select="$ccname"/>Type;

class <xsl:value-of select="$ccname"/>Controller extends ORMController
{
    /**
     * @{inheritDoc}
     */
    protected function getEntityName()
    {
        return '<xsl:value-of select="//entity/@name"/>';
    }

    /**
     * @{inheritDoc}
     */
    protected function getName()
    {
        return '<xsl:value-of select="$_name"/>';
    }

    /**
     * @{inheritDoc}
     */
    protected function getRolePrefix()
    {
        return 'ROLE_ADMIN_<xsl:value-of select="php:function('strtoupper', $_name)"/>_';
    }

    /**
     * @{inheritDoc}
     */
    protected function getRoutePrefix()
    {
        return '<xsl:value-of select="$routeprefix"/>_<xsl:value-of select="$_name"/>_';
    }

    /**
     * @{inheritDoc}
     */
    public function getFormType()
    {
        return new <xsl:value-of select="$ccname"/>Type();
    }

    /**
     * @{inheritDoc}
     */
    protected function getQueryBuilder(Request $request, array $filters = [])
    {
        $queryBuilder = $this
            ->get('doctrine')
            ->getRepository($this->getEntityName())
            ->createQueryBuilder('<xsl:value-of select="$_name"/>')
        ;
        <xsl:apply-templates select="//entity/field" mode="filters.query"/>
        return $queryBuilder;
    }

    /**
     * @{inheritDoc}
     */
    protected function getColumns()
    {
        $translator = $this->get('translator');
        <xsl:apply-templates select="//entity" mode="column.dateProcessor"/>
        return [<xsl:apply-templates select="//entity" mode="column"/>
        ];
    }

    /**
     * @{inheritDoc}
     */
    public function addRowExtraActions(array $data)
    {
        return [];
    }<xsl:apply-templates select="//entity/field" mode="filters.form"/>
}
</xsl:template>
</xsl:stylesheet>
