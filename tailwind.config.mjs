/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Outfit', 'sans-serif'],
        body: ['Manrope', 'sans-serif'],
      },
      colors: {
        'accent-yellow': '#FFD84D',
        'accent-blue': '#6EA8FE',
        'accent-green': '#7DFF8C',
        'accent-red': '#FF8A8A',
        'grid-gray': '#f2f2f2',
        'brutal-border': '#111111',
        'brutal-text': '#111111',
        'brutal-bg': '#FFFFFF',
      },
      borderRadius: {
        card: '24px',
        'card-lg': '32px',
        pill: '9999px',
      },
      borderWidth: {
        3: '3px',
        4: '4px',
      },
      boxShadow: {
        brutal: '8px 8px 0px 0px #111111',
        'brutal-sm': '4px 4px 0px 0px #111111',
        'brutal-lg': '12px 12px 0px 0px #111111',
        'brutal-hover': '12px 12px 0px 0px #111111',
        brutal2: '6px 6px 0px 0px #111111',
      },
      animation: {
        float: 'float 6s ease-in-out infinite',
        'float-delayed': 'float 6s ease-in-out 2s infinite',
        'float-slow': 'float 8s ease-in-out 1s infinite',
        'fade-in': 'fadeIn 0.6s ease-out forwards',
        'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
        'slide-up': 'slideUp 0.4s ease-out forwards',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%': { transform: 'translateY(-16px)' },
        },
        fadeIn: {
          from: { opacity: '0' },
          to: { opacity: '1' },
        },
        fadeInUp: {
          from: { opacity: '0', transform: 'translateY(30px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        slideUp: {
          from: { opacity: '0', transform: 'translateY(20px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
      },
    },
  },
  plugins: [],
};
