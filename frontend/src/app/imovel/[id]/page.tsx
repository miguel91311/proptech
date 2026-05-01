import ImovelClient from "./ImovelClient";

export function generateStaticParams() {
  return [{ id: "1" }, { id: "2" }, { id: "demo" }];
}

export default function ImovelPage() {
  return <ImovelClient />;
}
