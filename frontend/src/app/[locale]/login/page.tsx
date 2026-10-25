'use client';

import { zodResolver } from '@hookform/resolvers/zod';
import { useLocale, useTranslations } from 'next-intl';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Link, useRouter } from '@/i18n/navigation';
import { ApiClientError } from '@/lib/api/client';
import { login } from '@/lib/api/endpoints';
import { setStoredToken } from '@/lib/auth/token';

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
});

type LoginForm = z.infer<typeof loginSchema>;

export default function LoginPage() {
  const t = useTranslations('auth');
  const errors = useTranslations('errors');
  const locale = useLocale();
  const router = useRouter();
  const [formError, setFormError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors: fieldErrors, isSubmitting },
  } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (values: LoginForm) => {
    setFormError(null);
    try {
      const response = await login(values, locale);
      setStoredToken(response.data.token);
      router.push('/account');
      router.refresh();
    } catch (error) {
      if (error instanceof ApiClientError && error.code === 'INVALID_CREDENTIALS') {
        setFormError(errors('invalidCredentials'));
      } else {
        setFormError(errors('generic'));
      }
    }
  };

  return (
    <main className="mx-auto flex w-full max-w-md flex-col gap-6 px-4 py-12 sm:px-6">
      <Card>
        <CardHeader>
          <CardTitle>{t('loginTitle')}</CardTitle>
          <CardDescription>{t('loginTitle')}</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4">
            {formError ? <Alert variant="error">{formError}</Alert> : null}
            <Input
              label={t('email')}
              type="email"
              autoComplete="email"
              error={fieldErrors.email ? t('emailRequired') : undefined}
              {...register('email')}
            />
            <Input
              label={t('password')}
              type="password"
              autoComplete="current-password"
              error={fieldErrors.password ? t('passwordRequired') : undefined}
              {...register('password')}
            />
            <Button type="submit" loading={isSubmitting} className="w-full">
              {t('submitLogin')}
            </Button>
            <p className="text-center text-sm">
              <Link href="/forgot-password" className="text-[var(--color-scholar)] hover:underline">
                {t('forgotPassword')}
              </Link>
            </p>
          </form>
          <p className="mt-4 text-center text-sm text-[var(--color-ink-secondary)]">
            {t('noAccount')}{' '}
            <Link href="/register" className="text-[var(--color-scholar)] hover:underline">
              {t('submitRegister')}
            </Link>
          </p>
        </CardContent>
      </Card>
    </main>
  );
}
