import React, { useState } from 'react';
import NavbarLogin from '../components/NavbarLogin';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { FaArrowLeft } from 'react-icons/fa';

export default function Register() {
    const { t } = useTranslation();
    const navigate = useNavigate();

    // Estados para capturar os dados do formulário
    const [email, setEmail] = useState('');
    const [name, setName] = useState('');
    const [surname, setSurname] = useState('');
    const [password, setPassword] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();

        const formData = {
            email,
            name,
            surname,
            password
        };

        console.log('Dados para envio:', formData);

        // Aqui futuramente você chama sua API PHP
        // ex: axios.post('/api/register', formData)
    };

    return (
        <div style={{ fontFamily: 'Poppins, sans-serif', height: '100vh', position: 'relative' }}>
            <NavbarLogin />

            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%' }}>
                <div style={{ width: '320px' }}>

                    {/* Botão Voltar */}
                    <div
                        onClick={() => navigate('/')}
                        style={{ display: 'flex', alignItems: 'center', cursor: 'pointer', marginBottom: '20px' }}
                    >
                        <FaArrowLeft style={{ marginRight: '8px' }} />
                        <span>{t('to_go_back')}</span>
                    </div>

                    <h2 style={{ textAlign: 'center' }}>{t('register_title')}</h2>
                    <div style={{ width: '320px', border: '1px', borderColor: '#333' }}>
                        <form onSubmit={handleSubmit}>
                            <div style={{ marginBottom: '10px' }}>
                                <label>{t('email')}</label>
                                <input
                                    type="email"
                                    style={inputStyle}
                                    placeholder={t('email_model')}
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                />
                            </div>

                            <div style={{ display: 'flex', gap: '30px', marginBottom: '10px' }}>
                                <div style={{ flex: 1 }}>
                                    <label>{t('name')}</label>
                                    <input
                                        type="text"
                                        style={inputStyle}
                                        placeholder={t('name')}
                                        value={name}
                                        onChange={(e) => setName(e.target.value)}
                                    />
                                </div>
                                <div style={{ flex: 1 }}>
                                    <label>{t('surname')}</label>
                                    <input
                                        type="text"
                                        style={inputStyle}
                                        placeholder={t('surname')}
                                        value={surname}
                                        onChange={(e) => setSurname(e.target.value)}
                                    />
                                </div>
                            </div>

                            <div style={{ marginBottom: '20px' }}>
                                <label>{t('password')}</label>
                                <input
                                    type="password"
                                    style={inputStyle}
                                    placeholder={t('password')}
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                />
                            </div>

                            <p style={{ fontSize: '12px', color: '#555', marginBottom: '20px', lineHeight: '20px' }}>
                                {t('terms_text')}
                            </p>

                            <button type="submit" style={buttonStyle}>
                                {t('agree_create_account')}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}

const inputStyle = {
    width: '100%',
    padding: '10px',
    border: '1px solid #ccc',
    borderRadius: '5px',
    fontSize: '14px'
};

const buttonStyle = {
    width: '100%',
    padding: '12px',
    background: '#0061FF',
    color: 'white',
    border: 'none',
    borderRadius: '8px',
    cursor: 'pointer',
    fontWeight: 600,
    fontSize: '16px'
};
