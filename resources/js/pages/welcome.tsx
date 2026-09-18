import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-83.75 text-sm not-has-[nav]:hidden lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Log in
                                </Link>

                            </>
                        )}
                    </nav>
                </header>
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="flex w-full max-w-83.75 flex-col-reverse lg:max-w-4xl lg:flex-row">
                        {/* Content */}
                        <div className="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-5ow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                            <h1 className="mb-1 text-xl font-semibold tracking-tight">
                                Welcome to AssetHub
                            </h1>

                            <p className="mb-5 text-[#706f6c] dark:text-[#A1A09A]">
                                A simple way to manage your organization&apos;s IT assets.
                                <br />
                                Keep everything organized, assigned, and easy to track.
                            </p>

                            <ul className="mb-6 flex flex-col">
                                <li className="relative flex items-center gap-4 py-2 before:absolute before:top-1/2 before:bottom-0 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#f97316] dark:bg-[#fb923c]" />
                                        </span>
                                    </span>

                                    <span>
                                        Manage
                                        <span className="ml-1 font-medium text-[#f53003] dark:text-[#FF4433]">
                                            laptops, mobiles &amp; SIMs
                                        </span>
                                    </span>
                                </li>

                                <li className="relative flex items-center gap-4 py-2 before:absolute before:top-0 before:bottom-1/2 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#f97316] dark:bg-[#fb923c]" />
                                        </span>
                                    </span>

                                    <span>
                                        Track
                                        <span className="ml-1 font-medium text-[#f53003] dark:text-[#FF4433]">
                                            assignments, ownership &amp; status
                                        </span>
                                    </span>
                                </li>

                                <li className="relative flex items-center gap-4 py-2">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#f97316] dark:bg-[#fb923c]" />
                                        </span>
                                    </span>

                                    <span>
                                        Keep your
                                        <span className="ml-1 font-medium text-[#f53003] dark:text-[#FF4433]">
                                            IT inventory in one place
                                        </span>
                                    </span>
                                </li>
                            </ul>

                            <div className="flex gap-3 text-sm leading-normal">
                                <a
                                    href="/dashboard"
                                    className="inline-block rounded-sm border border-[#f53003] bg-[#f53003] px-5 py-2 text-sm font-medium leading-normal text-white transition hover:bg-[#d92700] dark:border-[#FF4433] dark:bg-[#FF4433] dark:hover:bg-[#e52f20]"
                                >
                                    Go to Dashboard
                                </a>

                                <a
                                    href="/assets"
                                    className="inline-block rounded-sm border border-[#e3e3e0] bg-white px-5 py-2 text-sm font-medium leading-normal text-[#1b1b18] transition hover:bg-[#f7f7f5] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#222220]"
                                >
                                    View Assets
                                </a>
                            </div>
                        </div>

                        {/* AssetHub Illustration */}
                        <div className="relative -mb-px aspect-335/376 w-full shrink-0 overflow-hidden rounded-t-lg bg-[#fff4ed] lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-109.5 lg:rounded-t-none lg:rounded-r-lg dark:bg-[#1f1008]">
                            <div className="absolute inset-0 flex items-center justify-center p-10">
                                <div className="relative h-full w-full">
                                    {/* Decorative background */}
                                    <div className="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#f53003]/10 blur-3xl dark:bg-[#FF4433]/10" />

                                    {/* Laptop */}
                                    <div className="absolute left-[8%] top-[20%] w-[62%] -rotate-6 transition-transform duration-700 hover:rotate-0">
                                        <div className="rounded-lg border-2 border-[#1b1b18] bg-[#f5f5f3] p-2 shadow-[8px_8px_0px_#1b1b18] dark:border-[#FF750F] dark:bg-[#2a1810] dark:shadow-[8px_8px_0px_#FF750F]">
                                            <div className="flex aspect-16/10 items-center justify-center rounded bg-[#1b1b18] dark:bg-[#110805]">
                                                <div className="text-3xl font-bold text-[#f53003]">
                                                    A
                                                </div>
                                            </div>
                                        </div>

                                        <div className="mx-[-8%] h-2 rounded-b-lg border-x-2 border-b-2 border-[#1b1b18] bg-[#d7d7d2] dark:border-[#FF750F] dark:bg-[#4b2818]" />
                                    </div>

                                    {/* Mobile */}
                                    <div className="absolute right-[8%] top-[12%] w-[27%] rotate-[8deg] transition-transform duration-700 hover:rotate-0">
                                        <div className="rounded-3xl border-2 border-[#1b1b18] bg-[#171716] p-2 shadow-[6px_6px_0px_#f53003] dark:border-[#FF750F] dark:bg-[#110805] dark:shadow-[6px_6px_0px_#FF750F]">
                                            <div className="aspect-9/18 rounded-[1.1rem] bg-[#fff2f2] p-2 dark:bg-[#2b110d]">
                                                <div className="mb-3 mx-auto h-1 w-8 rounded-full bg-[#1b1b18] dark:bg-[#FF750F]" />
                                                <div className="mt-10 text-center text-xl font-bold text-[#f53003] dark:text-[#FF750F]">
                                                    📱
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* SIM card */}
                                    <div className="absolute bottom-[18%] left-[14%] w-[25%] rotate-12 transition-transform duration-700 hover:rotate-3">
                                        <div className="aspect-[1.55/1] rounded-md border-2 border-[#1b1b18] bg-[#f8b803] p-3 shadow-[5px_5px_0px_#1b1b18] dark:border-[#FF750F] dark:bg-[#733000] dark:shadow-[5px_5px_0px_#FF750F]">
                                            <div className="h-full rounded border border-[#1b1b18]/40 dark:border-[#FF750F]/40">
                                                <div className="ml-2 mt-2 h-5 w-5 rounded-sm border border-[#1b1b18]/50 dark:border-[#FF750F]/50" />
                                            </div>
                                        </div>
                                    </div>

                                    {/* Domain / Cloud card */}
                                    <div className="absolute bottom-[8%] right-[5%] w-[48%] rotate-[-5deg] transition-transform duration-700 hover:rotate-0">
                                        <div className="rounded-lg border-2 border-[#1b1b18] bg-white p-4 shadow-[7px_7px_0px_#f53003] dark:border-[#FF750F] dark:bg-[#24110a] dark:shadow-[7px_7px_0px_#FF750F]">
                                            <div className="mb-2 flex items-center gap-2">
                                                <div className="h-3 w-3 rounded-full bg-[#f53003] dark:bg-[#FF750F]" />
                                                <div className="h-2 w-16 rounded bg-[#d7d7d2] dark:bg-[#733000]" />
                                            </div>

                                            <div className="font-mono text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                                example.com
                                            </div>

                                            <div className="mt-3 h-1.5 w-full rounded bg-[#f53003]/20 dark:bg-[#FF750F]/20" />
                                            <div className="mt-2 h-1.5 w-2/3 rounded bg-[#f53003]/20 dark:bg-[#FF750F]/20" />
                                        </div>
                                    </div>

                                    {/* Small floating status dots */}
                                    <div className="absolute left-[48%] top-[8%] h-3 w-3 rounded-full bg-[#f53003] shadow-[0_0_20px_#f53003] dark:bg-[#FF750F] dark:shadow-[0_0_20px_#FF750F]" />
                                    <div className="absolute bottom-[35%] right-[3%] h-2 w-2 rounded-full bg-[#f8b803]" />
                                    <div className="absolute bottom-[6%] left-[48%] h-2.5 w-2.5 rounded-full bg-[#f53003] dark:bg-[#FF750F]" />
                                </div>
                            </div>

                            <div className="absolute inset-0 rounded-t-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-t-none lg:rounded-r-lg dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]" />
                        </div>
                    </main>
                </div>
                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
