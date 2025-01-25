/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./templates/**/*.html.twig",
    "./assets/**/*.js",
    "./assets/**/*.css",
    "./assets/**/*.scss",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Poppins", "sans-serif"],
        noto: ["Noto Serif", "serif"],
      },
    },
    colors: {
      bk: {
        DEFAULT: "#f8f9fa",
        light: "#F7FFF7",
        dark: "#ced4da",
      },
      primary: {
        DEFAULT: "#3498db",
        light: "#90caf9",
        dark: "#ced4da",
      },
      secondary: {
        DEFAULT: "#1D1D1D",
        light: "#ffeaa7",
        dark: "#DD6031",
      },
      accent: {
        DEFAULT: "#f8312f",
        light: "#ff5733",
        dark: "#d32f2f",
      },
      gray: {
        DEFAULT: "#f8f9fa",
        light: "#f1f3f5",
        dark: "#ced4da",
      },
      white: "#ffffff",
      black: "#000000",
    },
  },
  plugins: [],
  screens: {
    sm: "640px",
    // => @media (min-width: 640px) { ... }

    md: "768px",
    // => @media (min-width: 768px) { ... }

    lg: "1024px",
    // => @media (min-width: 1024px) { ... }

    xl: "1280px",
    // => @media (min-width: 1280px) { ... }

    "2xl": "1536px",
    // => @media (min-width: 1536px) { ... }
  },
};
