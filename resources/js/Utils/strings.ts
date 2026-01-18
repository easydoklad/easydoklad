export const pluralize = (text: string, count: number): string => {
  const variants = text.split('|')

  let variant = text

  if (variants.length === 3) {
    variant = variants[2]

    if (count === 1) {
      variant = variants[0]
    } else if (count > 1 && count <= 4) {
      variant = variants[1]
    }
  }

  return variant.replace(/\:count/, `${count}`)
}
