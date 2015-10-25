<?php

namespace Wvs\SonataAdminBundle\AdminBehaviors;

use Sonata\AdminBundle\Form\FormMapper;

/**
 * Adds methods that adds a positionField within sonata admin class.
 *
 * Should be used inside class that extends Sonata\AdminBundle\Admin\Admin
 */
trait PositionField
{

    /**
     * @param FormMapper $formMapper
     * @param string $fieldName: name of the field that represents the position. Property should be a float.
     * @return null
     */
    protected function addFormMapperPositionField(FormMapper $formMapper, $fieldName = 'position')
    {

        $choices = $this->getPositionFieldChoices($fieldName);

        if (empty($choices)) {
            $formMapper->add($fieldName, 'hidden', ['attr' =>['value' => 13.37]]);
            return null;
        }

        // preset last position for new items
        if (!$this->id($this->getSubject())) {
            $lastPos = array_keys($choices)[count($choices) - 1];
            $this->getSubject()->{'set' . ucfirst($fieldName)}($lastPos);
        }

        $formMapper->add($fieldName, 'choice', ['choices' => $choices]);
    }

    protected function getPositionFieldChoices($fieldName = 'position')
    {

        $repos = $this->getConfigurationPool()->getContainer()->get('doctrine')->getRepository($this->getClass());
        $data = $repos->findAll();

        $id = null;

        if ($this->getSubject()) {
            $id = $this->id($this->getSubject());
        }


        $choices = [];

        // initial
        if (count($data) == 0) {
            return $choices;
        }

        // fist position
        if ($id && $data[0]->getId() == $id) {
            $choices[(string) ($data[0]->{'get' . ucfirst($fieldName)}())] = 'first (current position)';
        } else {
            $choices[(string) ($data[0]->{'get' . ucfirst($fieldName)}() / 2)] = 'first';
        }

        // other positions
        for ($i = 0; $i < count($data); $i++) {

            $valueSuffix = '';

            if ($i == count($data) -1) {
                $pos = $data[$i]->{'get' . ucfirst($fieldName)}() + .99;
            } else {

                $pos = ($data[$i]->{'get' . ucfirst($fieldName)}() + $data[$i + 1]->{'get' . ucfirst($fieldName)}()) / 2;
                if ($id && $data[$i + 1 ]->getId() == $id) {
                    $pos = $data[$i + 1]->{'get' . ucfirst($fieldName)}();
                    $valueSuffix = ' (current position)';
                }
            }

            if ($id && $data[$i]->getId() == $id) {
                continue;
            }

            $choices[(string) $pos] = 'after ' . $data[$i]->__toString() . $valueSuffix;

        }

        return $choices;

    }

}
