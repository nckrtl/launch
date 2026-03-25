import type { SVGAttributes } from "react";

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 42" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M31.4 29.4c-4.3 4.3-10.6 5.7-16.2 4.2L11 37.8c7.5 3.4 16.7 2 22.8-4.2 4.2-4.1 5.9-9.7 5.2-15L35.5 22c.2 2.7-.5 5.4-2.1 7.6-.6.8-1.3 1.4-2 2z"
                fill="currentColor"
            />
            <path
                d="M8.6 12.6c4.3-4.3 10.6-5.7 16.2-4.2L29 4.2C21.5.8 12.3 2.2 6.2 8.4 2 12.5.3 18.1 1 23.4L4.5 20c-.2-2.7.5-5.4 2.1-7.6.6-.8 1.3-1.4 2-1.8z"
                fill="currentColor"
            />
        </svg>
    );
}
