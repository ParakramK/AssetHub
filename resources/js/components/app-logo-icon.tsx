import { SVGAttributes } from 'react';

export function AssetHubLogo(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
            {/* Base Geometric "A" Structure */}
            <path d="M 70,10 L 126,130 L 98,130 L 70,70 L 42,130 L 14,130 Z" fill="#0F172A" />
            <path d="M 35,95 L 105,95 L 105,109 L 35,109 Z" fill="#0F172A" />

            {/* Network Node Lines */}
            <line x1="70" y1="20" x2="70" y2="70" stroke="#4F46E5" strokeWidth="4" strokeLinecap="round" />
            <line x1="70" y1="70" x2="17.5" y2="45" stroke="#4F46E5" strokeWidth="4" strokeLinecap="round" />
            <line x1="70" y1="70" x2="122.5" y2="45" stroke="#4F46E5" strokeWidth="4" strokeLinecap="round" />
            <line x1="70" y1="70" x2="28" y2="112" stroke="#14B8A6" strokeWidth="4" strokeLinecap="round" />
            <line x1="70" y1="70" x2="112" y2="112" stroke="#14B8A6" strokeWidth="4" strokeLinecap="round" />

            {/* Outer Nodes */}
            <circle cx="70" cy="20" r="8" fill="#4F46E5" />
            <circle cx="17.5" cy="45" r="8" fill="#4F46E5" />
            <circle cx="122.5" cy="45" r="8" fill="#4F46E5" />
            <circle cx="28" cy="112" r="8" fill="#14B8A6" />
            <circle cx="112" cy="112" r="8" fill="#14B8A6" />

            {/* Central Hub Node */}
            <circle cx="70" cy="70" r="11" fill="#14B8A6" stroke="#FFFFFF" strokeWidth="3" />
        </svg>
    );
}

export default AssetHubLogo;
