<?php

declare(strict_types=1);

namespace App;

use Illuminate\Support\Facades\Lang;

final class Example
{
    /**
     * @return list<mixed>
     */
    public function keys(string $dynamic): array
    {
        return [
            __('messages.welcome'),
            trans('messages.nested.deep'),
            trans_choice('messages.choice', 2),
            __('messages.types'),
            __("messages.double"),
            __("messages.{$dynamic}"),
            __('messages.' . $dynamic),
            __('messages.quoted'),
            __(
                'messages.multiline',
                ['name' => 'Example'],
            ),
            __('messages.only_in_en'),
            __('messages.list'),
            __('admin/users.title'),
            __('missing.group'),
            __('messages.missing_item'),
            __('vendor::messages.welcome'),
            __('A plain sentence.'),
            __('validation.custom.email.required'),
            Lang::get('messages.facade'),
            Lang::has('messages.facade'),
            \Illuminate\Support\Facades\Lang::get('messages.facade'),
            $this->__('messages.not_a_helper_call'),
        ];
    }

    public function __(string $key): string
    {
        return $key;
    }
}
