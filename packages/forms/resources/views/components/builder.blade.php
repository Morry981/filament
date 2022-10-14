<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $containers = $getChildComponentContainers();

        $isCloneable = $isCloneable();
        $isCollapsible = $isCollapsible();
        $isItemCreationDisabled = $isItemCreationDisabled();
        $isItemDeletionDisabled = $isItemDeletionDisabled();
        $isItemMovementDisabled = $isItemMovementDisabled();
    @endphp

    <div>
        @if ((count($containers) > 1) && $isCollapsible)
            <div class="space-x-2 rtl:space-x-reverse" x-data="{}">
                <x-forms::link
                    x-on:click="$dispatch('builder-collapse', '{{ $getStatePath() }}')"
                    tag="button"
                    size="sm"
                >
                    {{ __('forms::components.builder.buttons.collapse_all.label') }}
                </x-forms::link>

                <x-forms::link
                    x-on:click="$dispatch('builder-expand', '{{ $getStatePath() }}')"
                    tag="button"
                    size="sm"
                >
                    {{ __('forms::components.builder.buttons.expand_all.label') }}
                </x-forms::link>
            </div>
        @endif
    </div>

    <div x-data="{}" {{ $attributes->merge($getExtraAttributes())->class([
        'filament-forms-builder-component space-y-6 rounded-xl',
        'bg-gray-50 p-6' => $isInset(),
        'dark:bg-gray-500/10' => $isInset() && config('forms.dark_mode'),
    ]) }}>
        @if (count($containers))
            <ul
                class="space-y-12"
                x-sortable
                x-on:end.stop="$wire.dispatchFormEvent('builder::moveItems', '{{ $getStatePath() }}', $event.target.sortable.toArray())"
            >
                @php
                    $hasBlockLabels = $hasBlockLabels();
                    $hasBlockNumbers = $hasBlockNumbers();
                @endphp

                @foreach ($containers as $uuid => $item)
                    <li
                        x-data="{
                            isCreateButtonVisible: false,
                            isCollapsed: @js($isCollapsed()),
                        }"
                        x-on:builder-collapse.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = true)"
                        x-on:builder-expand.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = false)"
                        x-on:click="isCreateButtonVisible = true"
                        x-on:mouseenter="isCreateButtonVisible = true"
                        x-on:click.away="isCreateButtonVisible = false"
                        x-on:mouseleave="isCreateButtonVisible = false"
                        wire:key="{{ $this->id }}.{{ $item->getStatePath() }}.item"
                        x-sortable-item="{{ $uuid }}"
                        x-on:expand-concealing-component.window="
                            error = $el.querySelector('[data-validation-error]')

                            if (! error) {
                                return
                            }

                            isCollapsed = false

                            if (document.body.querySelector('[data-validation-error]') !== error) {
                                return
                            }

                            setTimeout(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'start' }), 200)
                        "
                        @class([
                            'bg-white border border-gray-300 shadow-sm rounded-xl relative',
                            'dark:bg-gray-800 dark:border-gray-600' => config('forms.dark_mode'),
                        ])
                    >
                        @if ((! $isItemMovementDisabled) || $hasBlockLabels || (! $isItemDeletionDisabled) || $isCollapsible || $isCloneable)
                            <header @class([
                                'flex items-center h-10 overflow-hidden border-b bg-gray-50 rounded-t-xl',
                                'dark:bg-gray-800 dark:border-gray-700' => config('forms.dark_mode'),
                            ])>
                                @unless ($isItemMovementDisabled)
                                    <button
                                        title="{{ __('forms::components.builder.buttons.move_item.label') }}"
                                        x-sortable-handle
                                        wire:keydown.prevent.arrow-up="dispatchFormEvent('builder::moveItemUp', '{{ $getStatePath() }}', '{{ $uuid }}')"
                                        wire:keydown.prevent.arrow-down="dispatchFormEvent('builder::moveItemDown', '{{ $getStatePath() }}', '{{ $uuid }}')"
                                        type="button"
                                        @class([
                                            'flex items-center justify-center flex-none w-10 h-10 text-gray-400 border-r rtl:border-l rtl:border-r-0 transition hover:text-gray-500',
                                            'dark:border-gray-700' => config('forms.dark_mode'),
                                        ])
                                    >
                                        <span class="sr-only">
                                            {{ __('forms::components.builder.buttons.move_item.label') }}
                                        </span>

                                        @svg('heroicon-m-arrows-up-down', 'h-4 w-4')
                                    </button>
                                @endunless

                                @if ($hasBlockLabels)
                                    <p @class([
                                        'flex-none px-4 text-xs font-medium text-gray-600 truncate',
                                        'dark:text-gray-400' => config('forms.dark_mode'),
                                    ])>
                                        @php
                                            $block = $item->getParentComponent();

                                            $block->labelState($item->getRawState());
                                        @endphp

                                        {{ $item->getParentComponent()->getLabel() }}

                                        @php
                                            $block->labelState(null);
                                        @endphp

                                        @if ($hasBlockNumbers)
                                            <small class="font-mono">{{ $loop->iteration }}</small>
                                        @endif
                                    </p>
                                @endif

                                <div class="flex-1"></div>

                                <ul @class([
                                    'flex divide-x rtl:divide-x-reverse',
                                    'dark:divide-gray-700' => config('forms.dark_mode'),
                                ])>
                                    @if ($isCloneable)
                                        <li>
                                            <button
                                                title="{{ __('forms::components.builder.buttons.clone_item.label') }}"
                                                wire:click="dispatchFormEvent('builder::cloneItem', '{{ $getStatePath() }}', '{{ $uuid }}')"
                                                type="button"
                                                @class([
                                                    'flex items-center justify-center flex-none w-10 h-10 text-gray-400 transition hover:text-gray-500',
                                                    'dark:border-gray-700' => config('forms.dark_mode'),
                                                ])
                                            >
                                                <span class="sr-only">
                                                    {{ __('forms::components.builder.buttons.clone_item.label') }}
                                                </span>

                                                @svg('heroicon-m-square-2-stack', 'h-4 w-4')
                                            </button>
                                        </li>
                                    @endif

                                    @unless ($isItemDeletionDisabled)
                                        <li>
                                            <button
                                                title="{{ __('forms::components.builder.buttons.delete_item.label') }}"
                                                wire:click="dispatchFormEvent('builder::deleteItem', '{{ $getStatePath() }}', '{{ $uuid }}')"
                                                type="button"
                                                @class([
                                                    'flex items-center justify-center flex-none w-10 h-10 text-danger-600 transition hover:text-danger-500',
                                                    'dark:text-danger-500 dark:hover:text-danger-400' => config('forms.dark_mode'),
                                                ])
                                            >
                                                <span class="sr-only">
                                                    {{ __('forms::components.builder.buttons.delete_item.label') }}
                                                </span>

                                                @svg('heroicon-m-trash', 'h-4 w-4')
                                            </button>
                                        </li>
                                    @endunless

                                    @if ($isCollapsible)
                                        <li>
                                            <button
                                                x-bind:title="(! isCollapsed) ? '{{ __('forms::components.builder.buttons.collapse_item.label') }}' : '{{ __('forms::components.builder.buttons.expand_item.label') }}'"
                                                x-on:click="isCollapsed = ! isCollapsed"
                                                type="button"
                                                class="flex items-center justify-center flex-none w-10 h-10 text-gray-400 transition hover:text-gray-500"
                                            >
                                                @svg(
                                                    'heroicon-m-minus',
                                                    'h-4 w-4',
                                                    ['x-show' => '! isCollapsed'],
                                                )

                                                <span class="sr-only" x-show="! isCollapsed">
                                                    {{ __('forms::components.builder.buttons.collapse_item.label') }}
                                                </span>

                                                @svg(
                                                    'heroicon-m-plus',
                                                    'h-4 w-4',
                                                    [
                                                        'x-show' => 'isCollapsed',
                                                        'x-cloak',
                                                    ],
                                                )

                                                <span class="sr-only" x-show="isCollapsed" x-cloak>
                                                    {{ __('forms::components.builder.buttons.expand_item.label') }}
                                                </span>
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </header>
                        @endif

                        <div class="p-6" x-show="! isCollapsed">
                            {{ $item }}
                        </div>

                        <div class="p-2 text-xs text-center text-gray-400" x-show="isCollapsed" x-cloak>
                            {{ __('forms::components.builder.collapsed') }}
                        </div>

                        @if ((! $loop->last) && (! $isItemCreationDisabled) && (! $isItemMovementDisabled))
                            <div
                                x-show="isCreateButtonVisible"
                                x-transition
                                class="absolute inset-x-0 bottom-0 flex items-center justify-center h-12 -mb-12"
                            >
                                <x-forms::dropdown>
                                    <x-slot name="trigger">
                                        <x-forms::icon-button
                                            :label="$getCreateItemBetweenButtonLabel()"
                                            icon="heroicon-m-plus"
                                        />
                                    </x-slot>

                                    <x-forms::builder.block-picker
                                        :blocks="$getBlocks()"
                                        :create-after-item="$uuid"
                                        :state-path="$getStatePath()"
                                    />
                                </x-forms::dropdown>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if (! $isItemCreationDisabled)
            <x-forms::dropdown class="flex justify-center">
                <x-slot name="trigger">
                    <x-forms::button size="sm">
                        {{ $getCreateItemButtonLabel() }}
                    </x-forms::button>
                </x-slot>

                <x-forms::builder.block-picker
                    :blocks="$getBlocks()"
                    :state-path="$getStatePath()"
                />
            </x-forms::dropdown>
        @endif
    </div>
</x-dynamic-component>
