<?php

/*
 * This file was copied from the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file at https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/LICENSE
 *
 * Original @author Ener-Getick <egetick@gmail.com>
 */

namespace App\Serializer;

// src/Serializer/FormErrorSerializer.php

//use Symfony\Component\Form\FormError;
//use Symfony\Component\Form\FormInterface;
//use Symfony\Component\Translation\TranslatorInterface;

use FOS\RestBundle\Serializer\Normalizer\FormErrorNormalizer as FosRestFormErrorNormalizer;

/**
 * Serializes invalid Form instances.
 */
class FormErrorSerializer extends FosRestFormErrorNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            parent::normalize($object, $format, $context)['errors']
        ];
    }
//    private $translator;
//
//    public function __construct(TranslatorInterface $translator)
//    {
//        $this->translator = $translator;
//    }
//
//    public function convertFormToArray(FormInterface $data)
//    {
//        $form = $errors = [];
//
//        foreach ($data->getErrors() as $error) {
//            $errors[] = $this->getErrorMessage($error);
//        }
//
//        if ($errors) {
//            $form['errors'] = $errors;
//        }
//
//        $children = [];
//        foreach ($data->all() as $child) {
//            if ($child instanceof FormInterface) {
//                $children[$child->getName()] = $this->convertFormToArray($child);
//            }
//        }
//
//        if ($children) {
//            $form['children'] = $children;
//        }
//
//        return $form;
//    }
//
//    private function getErrorMessage(FormError $error)
//    {
//        if (null !== $error->getMessagePluralization()) {
//            return $this->translator->transChoice(
//                $error->getMessageTemplate(),
//                $error->getMessagePluralization(),
//                $error->getMessageParameters(),
//                'validators'
//            );
//        }
//
//        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
//    }
}