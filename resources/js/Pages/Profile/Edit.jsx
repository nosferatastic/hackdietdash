import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import FitbitAuthForm from './Partials/FitbitAuthForm';
import UserTrackingSettingsForm from './Partials/UserTrackingSettingsForm';

import { Head } from '@inertiajs/react';

import Paper from '@mui/material/Paper';
export default function Edit({ auth, mustVerifyEmail, status }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>}
        >
            <Head title="Profile" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <Paper elevation={4} sx={{padding: '2em'}}>
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            className="max-w-xl"
                        />
                    </Paper>

                    <Paper elevation={4} sx={{padding: '2em'}}>
                        <FitbitAuthForm className="max-w-xl" />
                    </Paper>

                    <Paper elevation={4} sx={{padding: '2em'}}>
                        <UserTrackingSettingsForm className="max-w-xl" />
                    </Paper>

                    <Paper elevation={4} sx={{padding: '2em'}}>
                        <UpdatePasswordForm className="max-w-xl" />
                    </Paper>

                    <Paper elevation={4} sx={{padding: '2em'}}>
                        <DeleteUserForm className="max-w-xl" />
                    </Paper>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
