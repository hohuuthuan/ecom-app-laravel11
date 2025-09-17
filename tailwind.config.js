import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/ts/**/*.ts',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        primary: { 600: '#0ea5e9', 700: '#0284c7' },
      },
      borderRadius: { '2xl': '1rem' },
    },
  },
  plugins: [forms, typography],
};
