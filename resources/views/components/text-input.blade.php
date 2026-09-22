@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-[#2d2d44] dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition']) }}>