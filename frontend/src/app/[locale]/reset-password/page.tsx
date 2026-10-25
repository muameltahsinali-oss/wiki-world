'use client';

import { zodResolver } from '@hookform/resolvers/zod';
import { useLocale, useTranslations } from 'next-intl';
import { useSearchParams } from 'next/navigation';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Link, useRouter } from '@/i18n/navigation';
import { resetPassword } from '@/lib/api/endpoints';

const schema = z
  .object({
    email: z.string().email(),
    token: z.string().min(1),
    password: z.string().min(8),
    password_confirmation: z.string().min(8),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'mismatch',
    path: ['password_confirmation'],
  });

type FormValues = z.infer<typeof schema>;

export default function ResetPasswordPage() {
  const t = useTranslations('auth');
  const errors = useTranslations('errors');
  const locale = useLocale();
  const router = useRouter();
  const searchParams = useSearchParams();
  const [formError, setFormError] = useState<string | null>(null);
  const [done, setDone] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors: fieldErrors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      email: searchParams.get('email') ?? '',
      token: searchParams.get('token') ?? '',
    },
  });

  const onSubmit = async (values: FormValues) => {
    setFormError(null);
    try {
      await resetPassword(values, locale);
      setDone(true);
      setTimeout(() => router.push('/login'), 2000);
    } catch {
      setFormError(errors('generic'));
    }
  };

  return (
    <main className="mx-auto flex w-full max-w-md flex-col gap-6 px-4 py-12 sm:px-6">
      <Card>
        <CardHeader>
          <CardTitle>{t('resetPasswordTitle')}</CardTitle>
        </CardHeader>
        <CardContent>
          {done ? (
            <Alert variant="success">{t('resetSuccess')}</Alert>
          ) : (
            <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4">
              {formError ? <Alert variant="error">{formError}</Alert> : null}
              <Input label={t('email')} type="email" {...register('email')} />
              <Input label={t('token')} {...register('token')} />
              <Input label={t('password')} type="password" {...register('password')} />
              <Input
                label={t('passwordConfirmation')}
                type="password"
                error={
                  fieldErrors.password_confirmation ? t('passwordMismatch') : undefined
                }
                {...register('password_confirmation')}
              />
              <Button type="submit" loading={isSubmitting} className="w-full">
                {t('submitReset')}
              </Button>
            </form>
          )}
          <p className="mt-4 text-center text-sm">
            <Link href="/login" className="text-[var(--color-scholar)] hover:underline">
              {t('backToLogin')}
            </Link>
          </p>
        </CardContent>
      </Card>
    </main>
  );
}
