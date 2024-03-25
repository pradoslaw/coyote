SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
cd $SCRIPT_DIR

mkdir -p runner/
chown nginx:nginx runner/
cd runner/
yarn init --private -y
yarn add --dev tailwindcss 
yarn tailwindcss --content "../src/**/*.php"  -o "../../public/css/neon.css"
