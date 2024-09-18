<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Controller;

use IServ\Bundle\Flash\Flash\FlashInterface;
use IServ\CoreBundle\Controller\AbstractPageController;
use IServ\CoreBundle\Traits\LoggerTrait;
use Psr\Log\LoggerInterface;
use Stsbl\BillBoardBundle\Security\Privilege;
use Stsbl\BillBoardBundle\Traits\LoggerInitializationTrait;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/*
 * The MIT License
 *
 * Copyright 2021 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Controller for Bill-Board administrative page
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 *
 * @Route("/billboard/manage")
 */
final class AdminController extends AbstractPageController
{
    use LoggerTrait;
    use LoggerInitializationTrait;

    private const CONFIG_DIR = '/var/lib/stsbl/billboard/cfg/';
    private const FILE_RULES = 'rules.cfg';

    /**
     * Rules configuration page
     *
     * @Route("", name="manage_billboard")
     * @Template()
     */
    public function indexAction(): array
    {
        $this->denyAccessUnlessMangePrivilegeIsGranted();

        // track path
        // change breadcrumb depending on if user is logged in admin section or not
        if (!$this->isAdmin()) {
            $this->addBreadcrumb(_('Bill-Board'), $this->generateUrl('billboard_index'));
            $this->addBreadcrumb(_('Manage'));
        } else {
            $this->addBreadcrumb(_('Bill-Board'));
        }

        // changing extended template depending on you know already ;)
        if ($this->isAdmin()) {
            $bundle = '@IServAdmin';
            $isAdmin = true;
        } else {
            $bundle = '@IServCore';
            $isAdmin = false;
        }

        return ['rules_form' => $this->getRulesForm()->createView(),
            'bundle' => $bundle,
            'help' => 'https://it.stsbl.de/documentation/mods/stsbl-iserv-billboard',
            'is_admin' => $isAdmin,
        ];
    }

    /**
     * Write new rules text to file
     *
     * @Route("/update/rules", name="manage_billboard_update_rules")
     */
    public function updateRulesAction(Request $request, FlashInterface $flash, LoggerInterface $logger): RedirectResponse
    {
        $this->denyAccessUnlessMangePrivilegeIsGranted();

        $form = $this->getRulesForm();
        $form->handleRequest($request);

        if (!$form->isValid()) {
            $flash->error(_('Invalid rules text'));

            return $this->redirect($this->generateUrl('manage_billboard'));
        }

        $data = $form->getData();

        return $this->updateFile($data['rules'], self::FILE_RULES, $flash, $logger);
    }


    /**
     * Returns the current bill-board rules returns the default rules, if no rules text is set.
     */
    public static function getCurrentRules(): string
    {
        if (!is_file(self::CONFIG_DIR . self::FILE_RULES) || !is_readable(self::CONFIG_DIR . self::FILE_RULES)) {
            return self::getDefaultRules();
        }

        return file_get_contents(self::CONFIG_DIR . self::FILE_RULES);
    }

    /**
     * Returns the translated default bill-board rules. Used when no custom rules text is set
     */
    public static function getDefaultRules(): string
    {
        return _(
            'The bill-board is only intended for small things. Please dont\'t offer things which have a worth of more ' .
            'than 100 euro.'
        );
    }

    /**
     * Returns a Form to set the rules text.
     */
    private function getRulesForm(): FormInterface
    {
        $builder = $this->createFormBuilder();

        $builder
            ->setAction($this->generateUrl('manage_billboard_update_rules'))
            ->add('rules', TextareaType::class, [
                'label' => false,
                'data' => self::getCurrentRules(),
                'attr' => [
                    'rows' => 10,
                    'help_text' => _('Here you can enter rules, which are shown at the form for adding an entry to ' .
                        'the bill-board.')
                ],
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => _('Save'),
                'buttonClass' => 'btn-success',
                'icon' => 'ok'
            ])
        ;

        return $builder->getForm();
    }

    /**
     * Write $content to given file inside given folder and creates file and folders if necessary.
     */
    private function updateFile(string $content, string $filename, FlashInterface $flash, LoggerInterface $logger, string $folder = self::CONFIG_DIR): RedirectResponse
    {
        try {
            touch($folder . $filename);
            $file = new \SplFileObject($folder . $filename, 'w');
            $file->fwrite($content);
        } catch (\RuntimeException $e) {
            $flash->error(_p('billboard', 'This should never happen.'));

            $logger->error(sprintf(
                'Exception on writing rules file: %s',
                $e->getMessage()
            ), ['exception' => $e]);
        }

        if ($filename === self::FILE_RULES) {
            $logText = 'Regeln aktualisiert';
        } else {
            throw new \InvalidArgumentException('Unknown filename ' . $filename . '.');
        }

        $this->log($logText);

        $flash->success(_('Rules updated successfully.'));

        return $this->redirect($this->generateUrl('manage_billboard'));
    }

    /**
     * Checks if the user has the manage privilege.
     */
    private function denyAccessUnlessMangePrivilegeIsGranted(): void
    {
        // check privilege
        $this->denyAccessUnlessGranted(
            Privilege::BILLBOARD_MANAGE,
            null,
            'You need the `BILLBOARD_MANAGE` privilege to access this page.'
        );
    }

    /**
     * Checks if user is authenticated admin.
     */
    private function isAdmin(): bool
    {
        return $this->isGranted('IS_AUTHENTICATED_ADMIN');
    }
}
