import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

// Importa cada idioma com as chaves corretas
import ptBR from './locales/pt-BR.json';
import esES from './locales/es-ES.json';
import enUS from './locales/en-US.json';
import enGB from './locales/en-GB.json';

i18n
  .use(LanguageDetector) // Detecta o idioma do navegador automaticamente
  .use(initReactI18next)
  .init({
    resources: {
      'pt-BR': { translation: ptBR },
      'es-ES': { translation: esES },
      'en-US': { translation: enUS },
      'en-GB': { translation: enGB }
    },
    fallbackLng: 'en-US', // Define qual idioma usar caso não detecte nenhum
    interpolation: {
      escapeValue: false
    }
  });

export default i18n;
