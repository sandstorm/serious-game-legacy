<?php

return [
    'forms' => [
        'components' => [
            'record-finder' => [
                'actions' => [
                    'open-modal-action' => [
                        'label' => 'Select :modelLabel|Select :pluralModelLabel',
                        'submit-action-label' => 'Select',
                    ],
                ],
                'limited-list' => [
                    'more_list_items' => 'and :count more',
                ],
                'expandable-limited-list' => [
                    'collapse_list' => 'Show :count less',
                    'expand_list' => 'Show :count more',
                ],
                'placeholder' => 'No item selected yet.',
            ],
        ],
    ],
    'livewire' => [
        'record-finder-table' => [
            'table' => [
                'description' => [
                    'single' => 'Select :modelLabel',
                    'multiple' => 'Select :pluralModelLabel',
                ],
            ],
        ],
    ],
    'tables' => [
        'actions' => [
            'associate' => [
                'label' => 'Associate',
                'modal_heading' => 'Associate :modelLabel',
                'modal_submit_action_label' => 'Associate',
                'extra_modal_footer_actions' => [
                    'associate_another' => [
                        'label' => 'Associate & associate another',
                    ],
                ],
                'success_notification_title' => 'Associated',
            ],
            'attach' => [
                'label' => 'Attach',
                'modal_heading' => 'Attach :modelLabel',
                'modal_submit_action_label' => 'Attach',
                'extra_modal_footer_actions' => [
                    'attach_another' => [
                        'label' => 'Attach & attach another',
                    ],
                ],
                'success_notification_title' => 'Attached',
            ],
        ],
    ],
];
