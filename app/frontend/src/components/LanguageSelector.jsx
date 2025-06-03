import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { FaGlobe } from 'react-icons/fa';
import Modal from './Modal';

export default function LanguageSelector() {
  const { i18n, t } = useTranslation();
  const [showModal, setShowModal] = useState(false);

  const languages = [
    { code: 'en', label: 'English (United States)' },
    { code: 'pt', label: 'Português (Brasil)' }
  ];

  const changeLanguage = (lng) => {
    i18n.changeLanguage(lng);
    setShowModal(false);
  };

  return (
    <div style={{ position: 'absolute', top: 20, right: 20 }}>
      <button onClick={() => setShowModal(true)} style={{
        background: 'none',
        border: 'none',
        fontSize: '24px',
        cursor: 'pointer'
      }}>
        <FaGlobe />
      </button>

      <Modal
        visible={showModal}
        title={t('language_selector_title')}
        onClose={() => setShowModal(false)}
      >
        <div style={{
          display: 'flex',
          flexDirection: 'column',
          gap: '10px',
          marginTop: '20px'
        }}>
          {languages.map(lang => (
            <button
              key={lang.code}
              onClick={() => changeLanguage(lang.code)}
              style={{
                background: 'none',
                border: '1px solid #ccc',
                borderRadius: '4px',
                padding: '8px',
                cursor: 'pointer'
              }}
            >
              {lang.label}
            </button>
          ))}
        </div>
      </Modal>
    </div>
  );
}
