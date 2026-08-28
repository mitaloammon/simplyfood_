export default {
  content: ['./index.html', './src/**/*.{vue,ts}'],
  theme: {
    extend: {
      colors: {
        brand: {
          navy: '#14213D',
          blue: '#2563EB',
          mist: '#E8F0FE',
          floor: '#F4F6F8',
          white: '#FFFFFF',
          coral: '#DC5A4B',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
      boxShadow: {
        surface: '0 10px 30px rgba(20, 33, 61, 0.07)',
        lift: '0 16px 40px rgba(20, 33, 61, 0.12)',
      },
    },
  },
}
