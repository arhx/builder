<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $table->getLabel() }}
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-3">
        <div class="p-6 text-gray-900 dark:text-gray-100">
        </div>
    </div>
    <form class="auto-submit">
        {{ $table->render() }}
    </form>
</x-app-layout>
