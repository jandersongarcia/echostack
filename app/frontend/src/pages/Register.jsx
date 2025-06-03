import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { FaArrowLeft } from 'react-icons/fa';
import { TextField, Button, Typography, Box } from '@mui/material';
import axios from 'axios';

import NavbarLogin from '../components/NavbarLogin';
import TermsText from '../components/TermsText';
import Notification from '../components/Notification';
import PasswordInput from '../components/PasswordInput';
import Modal from '../components/Modal';
import api from '../api/api';

export default function Register() {
    const { t, i18n } = useTranslation();
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [notifications, setNotifications] = useState([]);

    const [formData, setFormData] = useState({
        email: '',
        name: '',
        surname: '',
        password: ''
    });

    const [modalData, setModalData] = useState({
        visible: false,
        title: '',
        content: ''
    });

    const handleChange = (field) => (e) => {
        setFormData({ ...formData, [field]: e.target.value });
    };

    const addNotification = (message, type = 'success') => {
        setNotifications(prev => [...prev, { message, type }]);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        const payload = { ...formData, language: i18n.language };

        try {
            const response = await api.post('register', payload);
            addNotification(t(response.data.message), 'success');
        } catch (error) {
            addNotification(t(error.response?.data?.message) || 'Erro desconhecido', 'error');
        } finally {
            setLoading(false);
        }
    };

    const fetchPostBySlug = async (slug) => {
        const response = await axios.get(`https://zarathia.com/wp-json/wp/v2/posts?slug=${slug}&_embed`);
        const post = response.data[0];
        return {
            title: post.title.rendered,
            content: post.content.rendered
        };
    };

    const handleOpenModal = async (slug) => {
        const data = await fetchPostBySlug(slug);
        setModalData({
            visible: true,
            title: data.title,
            content: data.content
        });
    };

    const handleCloseModal = () => {
        setModalData({ visible: false, title: '', content: '' });
    };

    return (
        <Box sx={{ fontFamily: 'Poppins, sans-serif', minHeight: '100vh', display: 'flex', flexDirection: 'column' }}>
            <NavbarLogin />

            {/* Notificações */}
            <Box sx={{ position: 'fixed', top: 20, right: 20, zIndex: 9999, display: 'flex', flexDirection: 'column', gap: 1 }}>
                {notifications.map((n, index) => (
                    <Notification
                        key={index}
                        message={n.message}
                        type={n.type}
                        onClose={() => setNotifications(prev => prev.filter((_, i) => i !== index))}
                    />
                ))}
            </Box>

            {/* Modal dinâmico */}
            <Modal visible={modalData.visible} title={modalData.title} onClose={handleCloseModal}>
                <div dangerouslySetInnerHTML={{ __html: modalData.content }} />
            </Modal>

            {/* Formulário */}
            <Box
                flex={1}
                display="flex"
                justifyContent="center"
                alignItems="center"
                sx={{
                    '@media (max-width: 600px)': {
                        alignItems: 'flex-start',
                        paddingTop: 4,
                        marginTop: '50px'
                    }
                }}
            >
                <Box width="100%" maxWidth="350px">
                    <Box display="flex" alignItems="center" mb={3} sx={{ cursor: 'pointer' }} onClick={() => navigate('/')}>
                        <FaArrowLeft style={{ marginRight: '8px' }} />
                        <Typography>{t('to_go_back')}</Typography>
                    </Box>

                    <Typography variant="h5" textAlign="center" mb={3}>{t('register_title')}</Typography>

                    <form onSubmit={handleSubmit}>
                        <Box display="flex" flexDirection="column" gap={2}>
                            <TextField fullWidth label={t('name')} value={formData.name} onChange={handleChange('name')} required />
                            <TextField fullWidth label={t('surname')} value={formData.surname} onChange={handleChange('surname')} required />
                            <TextField fullWidth label={t('email')} value={formData.email} onChange={handleChange('email')} autoComplete="new-username" required />
                            <PasswordInput label={t('password')} value={formData.password} onChange={handleChange('password')} required />

                            <TermsText
                                openTerms={() => handleOpenModal("fragmentos-de-cinza")}
                                openServices={() => handleOpenModal("zoe-a-semente-caida-de-elaila")}
                                openPrivacy={() => handleOpenModal("antes-de-todo-el-silencio")}
                            />

                            <Button type="submit" fullWidth variant="contained" size="large" sx={{ borderRadius: 2 }} disabled={loading}>
                                {t('agree_create_account')}
                            </Button>
                        </Box>
                    </form>
                </Box>
            </Box>
        </Box>
    );
}
