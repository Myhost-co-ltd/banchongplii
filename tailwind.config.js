/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: [
          'Prompt',
          'Noto Sans Thai',
          'Kanit',
          ...defaultTheme.fontFamily.sans,
        ],
      },
    },
  },
  plugins: [],
}

