import { useRef } from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import { usePage } from '@inertiajs/react';
import Button from '@mui/material/Button';

export function FitbitAuthButton(props) {
    return (
        <Button variant="contained" href={route('fitbit.auth')}>Connect</Button>
    );
}


export default function FitbitAuthForm({ className = '' }) {

    const user = usePage().props.auth.user;

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Fitbit Authorisation</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Your Fitbit account is currently {(user.fitbit_auth == null ? "not connected" : "connected" )}.
                </p>
                {(user.fitbit_auth == null ? <FitbitAuthButton /> : '')}
            </header>
        </section>
    );
}
