<?php

return [
    'forms' => [
        'components' => [
            'record-finder' => [
                'actions' => [
                    'open-modal-action' => [
                        'label' => 'Seleziona :modelLabel|Seleziona :pluralModelLabel',
                        'submit-action-label' => 'Seleziona',
                    ],
                ],
                'limited-list' => [
                    'more_list_items' => 'e altri :count',
                ],
                'expandable-limited-list' => [
                    'collapse_list' => 'Mostra :count in meno',
                    'expand_list' => 'Mostra altri :count',
                ],
                'placeholder' => 'Nessun elemento ancora selezionato.',
            ],
        ],
    ],
    'livewire' => [
        'record-finder-table' => [
            'table' => [
                'description' => [
                    'single' => 'Seleziona :modelLabel',
                    'multiple' => 'Seleziona :pluralModelLabel',
                ],
            ],
        ],
    ],
    'tables' => [
        'actions' => [
            'associate' => [
                'label' => 'Associa',
                'modal_heading' => 'Associa :modelLabel',
                'modal_submit_action_label' => 'Associa',
                'extra_modal_footer_actions' => [
                    'associate_another' => [
                        'label' => 'Associa & associa un altro',
                    ],
                ],
                'success_notification_title' => 'Associato',
            ],
            'attach' => [
                'label' => 'Collega',
                'modal_heading' => 'Collega :modelLabel',
                'modal_submit_action_label' => 'Collega',
                'extra_modal_footer_actions' => [
                    'attach_another' => [
                        'label' => 'Collega & collega un altro',
                    ],
                ],
                'success_notification_title' => 'Collegato',
            ],
        ],
    ],
];
