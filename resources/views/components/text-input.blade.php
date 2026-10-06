@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-[#27332C] dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 dark:[color-scheme:dark] focus:border-green-500 dark:focus:border-green-400 focus:ring-green-500 dark:focus:ring-green-400/40 disabled:opacity-60 disabled:cursor-not-allowed rounded-lg shadow-sm']) }}>
