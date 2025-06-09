import React, { useState } from 'react';
import {
  Box, Button, Checkbox, FormControl, FormLabel, Heading, Input, Stack, Text, Link, Flex, InputGroup, InputRightElement, IconButton, Progress
} from '@chakra-ui/react';
import { ViewIcon, ViewOffIcon } from '@chakra-ui/icons';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link as RouterLink } from 'react-router-dom';

export default function LoginAccountPage() {
  const { register, handleSubmit, watch } = useForm();
  const { t } = useTranslation();

  const [termsAccepted, setTermsAccepted] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const onSubmit = (data) => {
    console.log(data);
  };

  const password = watch('password') || '';

  // Função de verificação de força
  const checkPasswordStrength = (password) => {
    let score = 0;

    if (password.length >= 6) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    return score;
  };

  const strength = checkPasswordStrength(password);
  const isPasswordStrong = strength === 5;

  const isSubmitDisabled = !(termsAccepted && isPasswordStrong);

  // Gerar a cor da barra
  const getProgressColor = () => {
    if (strength <= 2) return 'red';
    if (strength === 3) return 'yellow';
    if (strength === 4) return 'blue';
    if (strength === 5) return 'green';
  };

  return (
    <Box maxW="md" mx="auto" mt={10} p={8} borderWidth={1} borderRadius="lg" boxShadow="lg">
      <Heading mb={1} textAlign="center">{t('createAccount')}</Heading>
      <form onSubmit={handleSubmit(onSubmit)}>
        <Stack spacing={4}>
          <Flex gap={2}>
            <FormControl>
              <FormLabel>{t('firstName')}</FormLabel>
              <Input type="text" {...register('firstName')} />
            </FormControl>
            <FormControl>
              <FormLabel>{t('lastName')}</FormLabel>
              <Input type="text" {...register('lastName')} />
            </FormControl>
          </Flex>

          <FormControl>
            <FormLabel>{t('email')}</FormLabel>
            <Input type="email"
            autoComplete='username'
            {...register('email')} />
          </FormControl>

          {/* Campo senha com toggle e barra de força */}
          <FormControl>
            <FormLabel>{t('password')}</FormLabel>
            <InputGroup>
              <Input
                autoComplete="current-password"
                type={showPassword ? 'text' : 'password'}
                {...register('password')}
              />
              <InputRightElement>
                <IconButton
                  variant="ghost"
                  size="sm"
                  icon={showPassword ? <ViewOffIcon /> : <ViewIcon />}
                  onClick={() => setShowPassword(!showPassword)}
                />
              </InputRightElement>
            </InputGroup>

            <Progress mt={2} value={(strength / 5) * 100} size="sm" colorScheme={getProgressColor()} />

            {!isPasswordStrong && password.length > 0 && (
              <Text fontSize="xs" color="red.500" mt={1}>
                {t('passwordPtrength')}
              </Text>
            )}
          </FormControl>

          <Checkbox onChange={(e) => setTermsAccepted(e.target.checked)}>
            {t('termsNotice')}
          </Checkbox>

          <Button
            type="submit"
            colorScheme="teal"
            size="lg"
            isDisabled={isSubmitDisabled}
          >
            {t('registerButton')}
          </Button>

          <Text textAlign="center" fontSize="sm">
            {t('alreadyHaveAccount')} <Link as={RouterLink} to="/" color="teal.500">{t('loginTitle')}</Link>
          </Text>
        </Stack>
      </form>
    </Box>
  );
}
