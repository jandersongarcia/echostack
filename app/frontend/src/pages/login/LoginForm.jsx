import React from 'react';
import {
  Box,
  Button,
  Checkbox,
  FormControl,
  FormLabel,
  Heading,
  Input,
  Link,
  Stack,
  Text
} from '@chakra-ui/react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link as RouterLink } from 'react-router-dom';

export default function LoginForm() {
  const { register, handleSubmit } = useForm();
  const { t } = useTranslation();

  const onSubmit = (data) => {
    console.log(data);
  };

  return (
    <Box minW="400px" maxW="md" mx="auto" mt={10} p={8} borderWidth={1} borderRadius="lg" boxShadow="lg">
      <Heading mb={1} textAlign="center">{t('loginTitle')}</Heading>
      <form onSubmit={handleSubmit(onSubmit)}>
        <Stack spacing={4}>
          <FormControl>
            <FormLabel>{t('email')}</FormLabel>
            <Input type="email" autoComplete='username' {...register('email')} />
          </FormControl>

          <FormControl>
            <FormLabel>{t('password')}</FormLabel>
            <Input type="password" autoComplete="current-password" {...register('password')} />
          </FormControl>

          <Link as={RouterLink} to="/recovery" alignSelf="flex-end" fontSize="sm" color="gray.500">{t('forgotPassword')}</Link>

          <Checkbox {...register('remember')}>{t('rememberMe')}</Checkbox>

          <Button type="submit" colorScheme="teal" size="lg">{t('loginButton')}</Button>

          <Link as={RouterLink} to="/register" color="teal.500">
            {t('registerNow')}
          </Link>
        </Stack>
      </form>
    </Box>
  );
}
