import React, { useState } from 'react';
import axios from 'axios';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { FaArrowLeft } from 'react-icons/fa';
import { TextField, Button, Typography, Box } from '@mui/material';

import NavbarLogin from '../components/NavbarLogin';
import TermsText from '../components/TermsText';
import Notification from '../components/Notification';
import api from '../api/api';


export default function Register() {
    const { t, i18n } = useTranslation();
    const navigate = useNavigate();
    const [notification, setNotification] = useState(null);

    const [email, setEmail] = useState('');
    const [name, setName] = useState('');
    const [surname, setSurname] = useState('');
    const [password, setPassword] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();

        const formData = {
            name: name,
            surname: surname,
            email: email,
            password: password,
            language: i18n.language
        };

        try {
            const response = await api.post('register', formData);
            console.log("Resposta do servidor:", response.data);
            setNotification(response.data.message);
            // redirecionar ou mostrar mensagem de sucesso aqui
        } catch (error) {
            console.error("Erro ao cadastrar:", error.response?.data || error.message);
            // mostrar erro para o usuário
        }
    };

    return (
        <Box sx={{ fontFamily: 'Poppins, sans-serif', minHeight: '100vh', display: 'flex', flexDirection: 'column' }}>
            <NavbarLogin />

            <Box flex={1} display="flex" justifyContent="center" alignItems="center">
                <Box width="100%" maxWidth="400px">

                    {/* Botão Voltar */}
                    <Box display="flex" alignItems="center" mb={3} sx={{ cursor: 'pointer' }} onClick={() => navigate('/')}>
                        <FaArrowLeft style={{ marginRight: '8px' }} />
                        <Typography>{t('to_go_back')}</Typography>
                    </Box>

                    <Typography variant="h5" textAlign="center" mb={3}>{t('register_title')}</Typography>

                    <form onSubmit={handleSubmit}>
                        <Box display="flex" flexDirection="column" gap={2}>

                            <TextField
                                fullWidth label={t('name')} placeholder={t('name')}
                                value={name} onChange={(e) => setName(e.target.value)}
                                required
                            />

                            <TextField
                                fullWidth label={t('surname')} placeholder={t('surname')}
                                value={surname} onChange={(e) => setSurname(e.target.value)}
                                required
                            />

                            <TextField
                                fullWidth label={t('email')} placeholder={t('email_model')}
                                value={email} onChange={(e) => setEmail(e.target.value)}
                                autoComplete="new-username"
                                required
                            />

                            <TextField
                                fullWidth type="password" label={t('password')} placeholder={t('password')}
                                value={password} onChange={(e) => setPassword(e.target.value)}
                                required
                                autoComplete="new-password"
                            />

                            <TermsText
                                openTerms={() => setShowTermsModal(true)}
                                openServices={() => setShowServicesModal(true)}
                                openPrivacy={() => setShowPrivacyModal(true)}
                            />

                            <Button type="submit" fullWidth variant="contained" size="large" sx={{ borderRadius: 2 }}>
                                {t('agree_create_account')}
                            </Button>
                        </Box>
                    </form>
                </Box>
            </Box>
        </Box>
    );
}
