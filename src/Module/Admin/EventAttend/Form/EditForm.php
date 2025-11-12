<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Module\Admin\EventAttend\Form;

use Lyrasoft\EventBooking\Enum\AttendState;
use Lyrasoft\EventBooking\Enum\EventOrderState;
use Lyrasoft\Luna\Field\UserModalField;
use Unicorn\Enum\BasicState;
use Unicorn\Field\CalendarField;
use Windwalker\Core\Language\TranslatorTrait;
use Windwalker\Form\Attributes\Fieldset;
use Windwalker\Form\Attributes\FormDefine;
use Windwalker\Form\Attributes\NS;
use Windwalker\Form\Field\ListField;
use Windwalker\Form\Field\TextareaField;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\Field\HiddenField;
use Windwalker\Form\Form;

class EditForm
{
    use TranslatorTrait;

    #[FormDefine]
    #[NS('item')]
    public function main(Form $form): void
    {
        $form->add('id', HiddenField::class);
    }

    #[FormDefine]
    #[Fieldset('basic')]
    #[NS('item')]
    public function basic(Form $form): void
    {
        $form->add('name', TextField::class)
            ->label('姓名')
            ->required(true);

        $form->add('email', TextField::class)
            ->label('電子郵件');

        $form->add('nick', TextField::class)
            ->label('暱稱');

        $form->add('mobile', TextField::class)
            ->label('行動電話');

        $form->add('phone', TextField::class)
            ->label('市話');

        $form->add('address', TextField::class)
            ->label('地址');
    }

    #[FormDefine]
    #[Fieldset('meta')]
    #[NS('item')]
    public function meta(Form $form): void
    {
        $form->add('no', TextField::class)
            ->label('報名序號')
            ->disabled(true);

        $form->add('state', ListField::class)
            ->label('簽到狀態')
            ->registerFromEnums(AttendState::class);

        $form->add('created', CalendarField::class)
            ->label($this->trans('unicorn.field.created'))
            ->disabled(true);

        $form->add('modified', CalendarField::class)
            ->label($this->trans('unicorn.field.modified'))
            ->disabled(true);

        $form->add('created_by', UserModalField::class)
            ->label('建立者')
            ->disabled(true);

        $form->add('modified_by', UserModalField::class)
            ->label('修改者')
            ->disabled(true);
    }
}
